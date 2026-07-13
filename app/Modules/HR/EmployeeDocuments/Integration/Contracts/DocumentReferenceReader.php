<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\Contracts;

use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;

interface DocumentReferenceReader
{
    public function describe(DocumentReferenceV1 $reference, string $actorReference): DocumentReferenceDescriptorV1;
}
