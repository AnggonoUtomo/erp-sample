<?php

namespace App\Modules\DocumentManagement\Foundation\Services;

use App\Modules\DocumentManagement\Foundation\DTO\CreateLogicalDocumentV1;
use App\Modules\DocumentManagement\Foundation\DTO\LogicalDocumentResultV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\IdempotencyKey;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Transactions\LogicalDocumentTransaction;
use DomainException;
use Illuminate\Support\Str;

class LogicalDocumentService
{
    private const IDEMPOTENCY_SCOPE = 'logical-document.create.v1';

    public function __construct(private LogicalDocumentTransaction $transaction) {}

    public function create(CreateLogicalDocumentV1 $request): LogicalDocumentResultV1
    {
        $keyHash = hash_hmac('sha256', $request->idempotencyKey, (string) config('app.key'));
        $fingerprint = $this->fingerprint($request);

        return $this->transaction->run(function () use ($request, $keyHash, $fingerprint): LogicalDocumentResultV1 {
            $existing = IdempotencyKey::query()
                ->where('scope', self::IDEMPOTENCY_SCOPE)
                ->where('key_hash', $keyHash)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if (! hash_equals($existing->request_fingerprint, $fingerprint)) {
                    throw new DomainException('Idempotency key was already used for a different request.');
                }

                return $this->result(LogicalDocument::query()->findOrFail($existing->document_id));
            }

            $owner = $request->ownerContext->toArray();
            $document = LogicalDocument::query()->create([
                'reference' => 'dms_'.Str::ulid(),
                'owner_schema_version' => $owner['schemaVersion'],
                'owner_domain' => $owner['domain'],
                'owner_aggregate_type' => $owner['aggregateType'],
                'owner_aggregate_id' => $owner['aggregateId'],
                'status' => 'PENDING',
                'created_by_reference' => $request->actorReference,
            ]);

            IdempotencyKey::query()->create([
                'scope' => self::IDEMPOTENCY_SCOPE,
                'key_hash' => $keyHash,
                'request_fingerprint' => $fingerprint,
                'document_id' => $document->getKey(),
                'status' => 'COMPLETED',
            ]);

            return $this->result($document);
        });
    }

    private function fingerprint(CreateLogicalDocumentV1 $request): string
    {
        return hash('sha256', json_encode([
            'ownerContext' => $request->ownerContext->toArray(),
            'actorReference' => $request->actorReference,
        ], JSON_THROW_ON_ERROR));
    }

    private function result(LogicalDocument $document): LogicalDocumentResultV1
    {
        return new LogicalDocumentResultV1(
            new DocumentReferenceV1($document->reference),
            $document->status,
        );
    }
}
