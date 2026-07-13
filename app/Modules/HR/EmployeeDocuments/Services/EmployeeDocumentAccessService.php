<?php

namespace App\Modules\HR\EmployeeDocuments\Services;

use App\Modules\DocumentManagement\Foundation\Delivery\DTO\DocumentDeliveryHandoffV1;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\IssueDocumentDeliveryV1;
use App\Modules\DocumentManagement\Foundation\Delivery\Exceptions\DocumentDeliveryDenied;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentDeliveryGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAccessDenied;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use Throwable;

class EmployeeDocumentAccessService
{
    public function __construct(private DocumentDeliveryGateway $delivery) {}

    public function issueDownload(EmployeeDocument $document, string $actorReference): DocumentDeliveryHandoffV1
    {
        if ($document->document_reference === null || $document->document_reference_version !== 1) {
            throw new EmployeeDocumentAccessDenied;
        }

        try {
            return $this->delivery->issue(new IssueDocumentDeliveryV1(
                new DocumentReferenceV1($document->document_reference),
                $actorReference,
                'DOWNLOAD',
                new DocumentOwnerContextV1('HR', 'EmployeeDocument', (string) $document->getKey()),
            ));
        } catch (DocumentDeliveryDenied) {
            throw new EmployeeDocumentAccessDenied;
        } catch (Throwable $exception) {
            report($exception);
            throw new EmployeeDocumentAccessDenied;
        }
    }
}
