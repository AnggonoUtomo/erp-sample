<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\Adapters;

use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentIngestionGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use App\Modules\HR\EmployeeDocuments\Integration\Contracts\EmployeeDocumentAttachmentGateway;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\EmployeeDocumentOwnerContextV1;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAttachmentUnavailable;
use Throwable;

final class DocumentManagementEmployeeDocumentAttachmentAdapter implements EmployeeDocumentAttachmentGateway
{
    public function __construct(private DocumentIngestionGateway $ingestion) {}

    public function createFor(AttachEmployeeDocumentV1 $request): DocumentReferenceV1
    {
        $owner = EmployeeDocumentOwnerContextV1::forDocument($request->employeeDocumentId)->toArray();

        try {
            $result = $this->ingestion->ingest(new IngestDocumentV1(
                new DocumentOwnerContextV1($owner['domain'], $owner['aggregateType'], $owner['aggregateId']),
                new UploadIntentV1(
                    $request->filename,
                    $request->declaredMediaType,
                    $request->declaredByteSize,
                    $request->idempotencyKey,
                ),
                $request->actorReference,
                $request->stream,
            ));
        } catch (Throwable $exception) {
            throw new EmployeeDocumentAttachmentUnavailable('Document attachment is unavailable.', previous: $exception);
        }

        if ($result->status !== 'AVAILABLE') {
            throw new EmployeeDocumentAttachmentUnavailable('Document attachment is unavailable.');
        }

        return new DocumentReferenceV1($result->reference->value());
    }
}
