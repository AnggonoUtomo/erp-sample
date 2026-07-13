<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\Contracts;

use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;

interface EmployeeDocumentAttachmentGateway
{
    public function createFor(AttachEmployeeDocumentV1 $request): DocumentReferenceV1;
}
