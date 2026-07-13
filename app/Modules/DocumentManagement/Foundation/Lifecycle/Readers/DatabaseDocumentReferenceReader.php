<?php

namespace App\Modules\DocumentManagement\Foundation\Lifecycle\Readers;

use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use Throwable;

class DatabaseDocumentReferenceReader implements DocumentReferenceReader
{
    public function __construct(private StorageAdapter $storage) {}

    public function describe(DocumentReferenceV1 $reference, string $actorReference): DocumentReferenceDescriptorV1
    {
        if (! $this->isSafeActorReference($actorReference)) {
            return new DocumentReferenceDescriptorV1($reference, 'DENIED');
        }

        $document = LogicalDocument::withTrashed()->where('reference', $reference->value())->first();
        if ($document === null) {
            return new DocumentReferenceDescriptorV1($reference, 'MISSING');
        }
        if ($document->trashed() || $document->status === 'ARCHIVED') {
            return new DocumentReferenceDescriptorV1($reference, 'ARCHIVED');
        }
        if ($document->status !== 'AVAILABLE' || $document->current_version_id === null) {
            return new DocumentReferenceDescriptorV1($reference, 'UNAVAILABLE');
        }

        $version = DocumentVersion::query()
            ->whereKey($document->current_version_id)
            ->where('document_id', $document->getKey())
            ->where('status', 'AVAILABLE')
            ->first();
        if ($version === null) {
            return new DocumentReferenceDescriptorV1($reference, 'UNAVAILABLE');
        }

        try {
            $available = $this->storage->exists(new StorageObjectKeyV1($version->storage_object_key));
        } catch (Throwable) {
            $available = false;
        }

        return new DocumentReferenceDescriptorV1($reference, $available ? 'AVAILABLE' : 'UNAVAILABLE');
    }

    private function isSafeActorReference(string $actorReference): bool
    {
        return trim($actorReference) !== ''
            && mb_strlen($actorReference) <= 255
            && preg_match('/[\x00-\x1F\x7F]/', $actorReference) !== 1;
    }
}
