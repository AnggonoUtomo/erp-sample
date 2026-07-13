<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\Contracts;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;

interface DocumentReferenceReader
{
    public function describe(DocumentReferenceV1 $reference, string $actorReference): DocumentReferenceDescriptorV1;
}
