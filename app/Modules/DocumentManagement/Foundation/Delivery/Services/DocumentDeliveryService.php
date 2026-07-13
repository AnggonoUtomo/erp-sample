<?php

namespace App\Modules\DocumentManagement\Foundation\Delivery\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessRequestV1;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\DocumentDeliveryHandoffV1;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\DocumentDeliveryPayloadV1;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\IssueDocumentDeliveryV1;
use App\Modules\DocumentManagement\Foundation\Delivery\Exceptions\DocumentDeliveryDenied;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentAccessGateway;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentDeliveryGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DeliveryToken;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class DocumentDeliveryService implements DocumentDeliveryGateway
{
    public function __construct(
        private DocumentAccessGateway $access,
        private StorageAdapter $storage,
        private AuditLogService $audit,
    ) {}

    public function issue(IssueDocumentDeliveryV1 $request): DocumentDeliveryHandoffV1
    {
        $decision = $this->access->decide(new DocumentAccessRequestV1(
            $request->reference,
            $request->actorReference,
            $request->action,
            $request->expectedOwner,
        ));
        if ($decision->state !== 'AVAILABLE') {
            throw new DocumentDeliveryDenied;
        }

        $ttlSeconds = (int) config('document-management.delivery.ttl_seconds');
        if ($ttlSeconds < 30 || $ttlSeconds > 900) {
            throw new RuntimeException('Document delivery TTL configuration is outside the safe range.');
        }

        return DB::transaction(function () use ($request, $ttlSeconds): DocumentDeliveryHandoffV1 {
            $document = LogicalDocument::query()
                ->where('reference', $request->reference->value())
                ->lockForUpdate()
                ->first();
            $version = $document === null ? null : DocumentVersion::query()
                ->whereKey($document->current_version_id)
                ->where('document_id', $document->getKey())
                ->where('status', 'AVAILABLE')
                ->first();
            if ($document === null || $document->status !== 'AVAILABLE' || $version === null) {
                throw new DocumentDeliveryDenied;
            }

            $token = $this->randomToken();
            $expiresAt = now()->addSeconds($ttlSeconds)->toImmutable();
            $record = DeliveryToken::query()->create([
                'token_hash' => $this->tokenHash($token),
                'document_id' => $document->getKey(),
                'version_id' => $version->getKey(),
                'owner_fingerprint' => $this->ownerFingerprint($request->expectedOwner),
                'actor_reference' => $request->actorReference,
                'action' => $request->action,
                'expires_at' => $expiresAt,
            ]);

            $this->audit->record(
                module: 'document-management.foundation',
                event: 'DocumentDelivery.issued',
                auditable: $record,
                description: 'Issued one-time document delivery handoff.',
                newValues: [
                    'reference' => $request->reference->value(),
                    'action' => $request->action,
                    'expires_at' => $expiresAt->toIso8601String(),
                ],
                actor: $this->actor($request->actorReference),
                fallbackToAuthenticatedActor: false,
            );

            return new DocumentDeliveryHandoffV1(
                $request->reference,
                $request->action,
                $token,
                $expiresAt,
            );
        });
    }

    public function consume(string $token, string $actorReference, string $action): DocumentDeliveryPayloadV1
    {
        $action = strtoupper(trim($action));
        if (! $this->validToken($token) || preg_match('/^user:[1-9][0-9]*$/', $actorReference) !== 1
            || $action !== 'DOWNLOAD') {
            throw new DocumentDeliveryDenied;
        }

        return DB::transaction(function () use ($token, $actorReference, $action): DocumentDeliveryPayloadV1 {
            $record = DeliveryToken::query()->where('token_hash', $this->tokenHash($token))->lockForUpdate()->first();
            if ($record === null || ! hash_equals($record->actor_reference, $actorReference)
                || ! hash_equals($record->action, $action) || $record->used_at !== null
                || $record->revoked_at !== null || $record->expires_at->isPast()) {
                throw new DocumentDeliveryDenied;
            }

            $document = LogicalDocument::query()->find($record->document_id);
            $version = DocumentVersion::query()->whereKey($record->version_id)
                ->where('document_id', $record->document_id)
                ->where('status', 'AVAILABLE')
                ->first();
            if ($document === null || $version === null || $document->current_version_id !== $version->getKey()) {
                throw new DocumentDeliveryDenied;
            }

            try {
                $owner = new DocumentOwnerContextV1(
                    $document->owner_domain,
                    $document->owner_aggregate_type,
                    $document->owner_aggregate_id,
                );
            } catch (InvalidArgumentException) {
                throw new DocumentDeliveryDenied;
            }
            if (! hash_equals($record->owner_fingerprint, $this->ownerFingerprint($owner))) {
                throw new DocumentDeliveryDenied;
            }
            $decision = $this->access->decide(new DocumentAccessRequestV1(
                new DocumentReferenceV1($document->reference),
                $actorReference,
                $action,
                $owner,
            ));
            if ($decision->state !== 'AVAILABLE') {
                throw new DocumentDeliveryDenied;
            }

            $stream = $this->storage->read(new StorageObjectKeyV1($version->storage_object_key));
            $record->update(['used_at' => now()]);
            $this->audit->record(
                module: 'document-management.foundation',
                event: 'DocumentDelivery.consumed',
                auditable: $record,
                description: 'Consumed one-time document delivery handoff.',
                newValues: ['reference' => $document->reference, 'action' => $action],
                actor: $this->actor($actorReference),
                fallbackToAuthenticatedActor: false,
            );

            return new DocumentDeliveryPayloadV1(
                $stream,
                $version->original_filename,
                $version->detected_media_type,
                $version->byte_size,
            );
        });
    }

    public function revoke(string $token, string $actorReference): void
    {
        if (! $this->validToken($token) || preg_match('/^user:[1-9][0-9]*$/', $actorReference) !== 1) {
            throw new DocumentDeliveryDenied;
        }

        DB::transaction(function () use ($token, $actorReference): void {
            $record = DeliveryToken::query()
                ->where('token_hash', $this->tokenHash($token))
                ->whereNull('used_at')
                ->whereNull('revoked_at')
                ->lockForUpdate()
                ->first();
            if ($record === null) {
                throw new DocumentDeliveryDenied;
            }

            $record->update(['revoked_at' => now(), 'revoked_by_reference' => $actorReference]);
            $this->audit->record(
                module: 'document-management.foundation',
                event: 'DocumentDelivery.revoked',
                auditable: $record,
                description: 'Revoked one-time document delivery handoff.',
                newValues: ['reference' => $record->document->reference, 'action' => $record->action],
                actor: $this->actor($actorReference),
                fallbackToAuthenticatedActor: false,
            );
        });
    }

    private function randomToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    private function validToken(string $token): bool
    {
        return preg_match('/^[A-Za-z0-9_-]{43}$/', $token) === 1;
    }

    private function tokenHash(string $token): string
    {
        return hash_hmac('sha256', $token, (string) config('app.key'));
    }

    private function ownerFingerprint(DocumentOwnerContextV1 $owner): string
    {
        return hash_hmac('sha256', json_encode($owner->toArray(), JSON_THROW_ON_ERROR), (string) config('app.key'));
    }

    private function actor(string $actorReference): ?User
    {
        return preg_match('/^user:([1-9][0-9]*)$/', $actorReference, $matches) === 1
            ? User::query()->find((int) $matches[1])
            : null;
    }
}
