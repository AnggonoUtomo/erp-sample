<?php

namespace App\Modules\DocumentManagement\Foundation\Access\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessDecisionV1;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessRequestV1;
use App\Modules\DocumentManagement\Foundation\Access\Policies\DocumentAccessPolicy;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentAccessGateway;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use Throwable;

class DocumentAccessDecisionService implements DocumentAccessGateway
{
    public function __construct(
        private DocumentAccessPolicy $policy,
        private StorageAdapter $storage,
        private AuditLogService $audit,
    ) {}

    public function decide(DocumentAccessRequestV1 $request): DocumentAccessDecisionV1
    {
        $actor = $this->policy->resolveActor($request);
        if ($actor === null || ! $this->policy->allows($actor, $request->action)) {
            return $this->decision($request, 'DENIED', actor: $actor);
        }

        $document = LogicalDocument::withTrashed()->where('reference', $request->reference->value())->first();
        if ($document === null) {
            return $this->decision($request, 'MISSING', actor: $actor);
        }
        if (! $this->ownerMatches($document, $request)) {
            return $this->decision($request, 'DENIED', $document, $actor);
        }
        if ($document->trashed() || $document->status === 'ARCHIVED') {
            return $this->decision($request, 'ARCHIVED', $document, $actor);
        }
        if ($document->status !== 'AVAILABLE' || $document->current_version_id === null) {
            return $this->decision($request, 'UNAVAILABLE', $document, $actor);
        }

        $version = DocumentVersion::query()
            ->whereKey($document->current_version_id)
            ->where('document_id', $document->getKey())
            ->where('status', 'AVAILABLE')
            ->first();
        if ($version === null || ! $this->objectExists($version->storage_object_key)) {
            return $this->decision($request, 'UNAVAILABLE', $document, $actor);
        }

        return $this->decision($request, 'AVAILABLE', $document, $actor);
    }

    private function ownerMatches(LogicalDocument $document, DocumentAccessRequestV1 $request): bool
    {
        $expected = $request->expectedOwner->toArray();

        return $document->owner_schema_version === $expected['schemaVersion']
            && hash_equals($document->owner_domain, $expected['domain'])
            && hash_equals($document->owner_aggregate_type, $expected['aggregateType'])
            && hash_equals($document->owner_aggregate_id, $expected['aggregateId']);
    }

    private function objectExists(string $objectKey): bool
    {
        try {
            return $this->storage->exists(new StorageObjectKeyV1($objectKey));
        } catch (Throwable) {
            return false;
        }
    }

    private function decision(
        DocumentAccessRequestV1 $request,
        string $state,
        ?LogicalDocument $document = null,
        ?User $actor = null,
    ): DocumentAccessDecisionV1 {
        $decision = new DocumentAccessDecisionV1($request->reference, $request->action, $state);
        $this->audit->record(
            module: 'document-management.foundation',
            event: $state === 'AVAILABLE' ? 'DocumentAccess.granted' : 'DocumentAccess.denied',
            auditable: $document,
            description: $state === 'AVAILABLE' ? 'Document access granted.' : 'Document access denied.',
            newValues: [
                'reference' => $request->reference->value(),
                'action' => $request->action,
                'state' => $state,
            ],
            actor: $actor,
            fallbackToAuthenticatedActor: false,
        );

        return $decision;
    }
}
