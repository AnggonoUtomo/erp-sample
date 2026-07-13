<?php

namespace App\Modules\DocumentManagement\Foundation\Lifecycle\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class DocumentLifecycleService
{
    public function __construct(
        private StorageAdapter $storage,
        private DocumentReferenceReader $reader,
        private AuditLogService $audit,
    ) {}

    public function archive(
        DocumentReferenceV1 $reference,
        string $actorReference,
        string $reason,
    ): DocumentReferenceDescriptorV1 {
        $this->assertActorReference($actorReference);
        $reason = trim($reason);
        if ($reason === '' || mb_strlen($reason) > 1000 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $reason) === 1) {
            throw new InvalidArgumentException('Archive reason must contain 1 through 1000 safe characters.');
        }

        return DB::transaction(function () use ($reference, $actorReference, $reason): DocumentReferenceDescriptorV1 {
            $document = $this->lockedDocument($reference);
            if ($document->trashed() || $document->status === 'ARCHIVED') {
                return new DocumentReferenceDescriptorV1($reference, 'ARCHIVED');
            }
            if ($document->status !== 'AVAILABLE' || $document->current_version_id === null) {
                throw new DomainException('Only an available document can be archived.');
            }

            $document->update(['status' => 'ARCHIVED']);
            $document->delete();
            $this->audit->record(
                module: 'document-management.foundation',
                event: 'Document.archived',
                auditable: $document,
                description: "Archived logical document #{$document->getKey()}",
                oldValues: ['status' => 'AVAILABLE'],
                newValues: ['status' => 'ARCHIVED', 'actor_reference' => $actorReference, 'reason' => $reason],
            );

            return new DocumentReferenceDescriptorV1($reference, 'ARCHIVED');
        });
    }

    public function restore(
        DocumentReferenceV1 $reference,
        string $actorReference,
    ): DocumentReferenceDescriptorV1 {
        $this->assertActorReference($actorReference);

        return DB::transaction(function () use ($reference, $actorReference): DocumentReferenceDescriptorV1 {
            $document = $this->lockedDocument($reference);
            if (! $document->trashed() && $document->status === 'AVAILABLE') {
                return $this->reader->describe($reference, $actorReference);
            }
            if (! $document->trashed() || $document->status !== 'ARCHIVED' || $document->current_version_id === null) {
                throw new DomainException('Only an archived document can be restored.');
            }

            $this->assertValidOwnerContext($document);

            $version = DocumentVersion::query()
                ->whereKey($document->current_version_id)
                ->where('document_id', $document->getKey())
                ->where('status', 'AVAILABLE')
                ->first();
            if ($version === null || ! $this->objectExists($version->storage_object_key)) {
                throw new DomainException('Archived document current version is unavailable.');
            }

            $document->restore();
            $document->update(['status' => 'AVAILABLE']);
            $this->audit->record(
                module: 'document-management.foundation',
                event: 'Document.restored',
                auditable: $document,
                description: "Restored logical document #{$document->getKey()}",
                oldValues: ['status' => 'ARCHIVED'],
                newValues: ['status' => 'AVAILABLE', 'actor_reference' => $actorReference],
            );

            return new DocumentReferenceDescriptorV1($reference, 'AVAILABLE');
        });
    }

    private function lockedDocument(DocumentReferenceV1 $reference): LogicalDocument
    {
        return LogicalDocument::withTrashed()
            ->where('reference', $reference->value())
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function objectExists(string $objectKey): bool
    {
        try {
            return $this->storage->exists(new StorageObjectKeyV1($objectKey));
        } catch (Throwable) {
            return false;
        }
    }

    private function assertActorReference(string $actorReference): void
    {
        if (trim($actorReference) === '' || mb_strlen($actorReference) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $actorReference) === 1) {
            throw new InvalidArgumentException('Actor reference must contain 1 through 255 safe characters.');
        }
    }

    private function assertValidOwnerContext(LogicalDocument $document): void
    {
        try {
            if ($document->owner_schema_version !== 1) {
                throw new InvalidArgumentException('Unsupported owner context schema version.');
            }
            new DocumentOwnerContextV1(
                $document->owner_domain,
                $document->owner_aggregate_type,
                $document->owner_aggregate_id,
            );
        } catch (InvalidArgumentException) {
            throw new DomainException('Archived document owner context is invalid.');
        }
    }
}
