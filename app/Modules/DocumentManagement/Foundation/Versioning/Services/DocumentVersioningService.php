<?php

namespace App\Modules\DocumentManagement\Foundation\Versioning\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestionResultV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionAvailability;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\IdempotencyKey;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StagedObjectV1;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\ValidatedUploadV1;
use App\Modules\DocumentManagement\Foundation\Upload\Policies\DocumentUploadPolicy;
use App\Modules\DocumentManagement\Foundation\Upload\Services\UploadStreamHasher;
use App\Modules\DocumentManagement\Foundation\Versioning\DTO\ReplaceDocumentVersionV1;
use DomainException;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class DocumentVersioningService
{
    private const IDEMPOTENCY_SCOPE = 'document-version-replacement.v1';

    public function __construct(
        private DocumentUploadPolicy $policy,
        private UploadStreamHasher $hasher,
        private StorageAdapter $storage,
        private AuditLogService $audit,
        private DocumentIngestionAvailability $availability,
    ) {}

    public function replace(ReplaceDocumentVersionV1 $request): IngestionResultV1
    {
        $this->availability->assertEnabled();
        $validated = $this->policy->validate($request->uploadIntent, $request->stream);
        $checksum = $this->hasher->sha256($request->stream, $validated->byteSize);
        $keyHash = hash_hmac('sha256', $request->uploadIntent->idempotencyKey, (string) config('app.key'));
        $fingerprint = $this->fingerprint($request, $validated, $checksum);

        $existing = $this->existing($keyHash, $fingerprint);
        if ($existing !== null) {
            return $existing;
        }

        $staged = $this->storage->stage($request->stream);
        $published = null;
        $replayed = false;

        try {
            $result = DB::transaction(function () use (
                $request, $validated, $checksum, $keyHash, $fingerprint, $staged, &$published, &$replayed,
            ): IngestionResultV1 {
                $document = LogicalDocument::query()
                    ->where('reference', $request->reference->value())
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($document->status !== 'AVAILABLE' || $document->current_version_id === null) {
                    throw new DomainException('Only an available document can receive a replacement version.');
                }

                $idempotency = IdempotencyKey::query()
                    ->where('scope', self::IDEMPOTENCY_SCOPE)
                    ->where('key_hash', $keyHash)
                    ->lockForUpdate()
                    ->first();
                if ($idempotency !== null) {
                    $replayed = true;

                    return $this->resultFromIdempotency($idempotency, $fingerprint);
                }

                $nextNumber = ((int) DocumentVersion::query()
                    ->where('document_id', $document->getKey())
                    ->max('version_number')) + 1;
                $version = DocumentVersion::query()->create([
                    'document_id' => $document->getKey(),
                    'version_number' => $nextNumber,
                    'storage_object_key' => $staged->key->value(),
                    'original_filename' => $validated->filename,
                    'declared_media_type' => $validated->declaredMediaType,
                    'detected_media_type' => $validated->detectedMediaType,
                    'byte_size' => $validated->byteSize,
                    'sha256' => $checksum,
                    'status' => 'STAGED',
                    'scan_status' => $validated->scanStatus,
                    'created_by_reference' => $request->actorReference,
                ]);
                $idempotency = IdempotencyKey::query()->create([
                    'scope' => self::IDEMPOTENCY_SCOPE,
                    'key_hash' => $keyHash,
                    'request_fingerprint' => $fingerprint,
                    'document_id' => $document->getKey(),
                    'version_id' => $version->getKey(),
                    'status' => 'PROCESSING',
                ]);

                $previousVersionId = $document->current_version_id;
                $published = $this->storage->promote($staged);
                $version->update(['storage_object_key' => $published->value(), 'status' => 'AVAILABLE']);
                $document->update(['current_version_id' => $version->getKey()]);
                $idempotency->update(['status' => 'COMPLETED']);

                $this->audit->record(
                    module: 'document-management.foundation',
                    event: 'DocumentVersion.replaced',
                    auditable: $version,
                    description: "Published replacement version #{$version->getKey()}",
                    oldValues: ['current_version_id' => $previousVersionId],
                    newValues: [
                        'reference' => $document->reference,
                        'version' => $version->version_number,
                        'status' => 'AVAILABLE',
                        'scan_status' => $version->scan_status,
                    ],
                );

                return $this->result($document, $version->refresh());
            });

            if ($replayed) {
                $this->storage->deleteStaged($staged);
            }

            return $result;
        } catch (Throwable $exception) {
            $this->cleanup($staged, $published);
            throw $exception;
        }
    }

    private function existing(string $keyHash, string $fingerprint): ?IngestionResultV1
    {
        $idempotency = IdempotencyKey::query()
            ->where('scope', self::IDEMPOTENCY_SCOPE)
            ->where('key_hash', $keyHash)
            ->first();

        return $idempotency === null ? null : $this->resultFromIdempotency($idempotency, $fingerprint);
    }

    private function resultFromIdempotency(IdempotencyKey $idempotency, string $fingerprint): IngestionResultV1
    {
        if (! hash_equals($idempotency->request_fingerprint, $fingerprint)) {
            throw new DomainException('Idempotency key was already used for a different replacement request.');
        }
        if ($idempotency->status !== 'COMPLETED' || $idempotency->version_id === null) {
            throw new RuntimeException('Previous replacement request is not in a reusable state.');
        }

        return $this->result(
            LogicalDocument::query()->findOrFail($idempotency->document_id),
            DocumentVersion::query()->findOrFail($idempotency->version_id),
        );
    }

    private function result(LogicalDocument $document, DocumentVersion $version): IngestionResultV1
    {
        return new IngestionResultV1(
            new DocumentReferenceV1($document->reference),
            $version->version_number,
            $version->status,
            $version->scan_status,
        );
    }

    private function fingerprint(ReplaceDocumentVersionV1 $request, ValidatedUploadV1 $validated, string $checksum): string
    {
        return hash('sha256', json_encode([
            'reference' => $request->reference->value(),
            'filename' => $validated->filename,
            'mediaType' => $validated->detectedMediaType,
            'byteSize' => $validated->byteSize,
            'sha256' => $checksum,
            'actorReference' => $request->actorReference,
        ], JSON_THROW_ON_ERROR));
    }

    private function cleanup(StagedObjectV1 $staged, ?StorageObjectKeyV1 $published): void
    {
        try {
            if ($published !== null) {
                $this->storage->deleteFailedObject($published);
            } else {
                $this->storage->deleteStaged($staged);
            }
        } catch (Throwable $cleanupException) {
            report($cleanupException);
        }
    }
}
