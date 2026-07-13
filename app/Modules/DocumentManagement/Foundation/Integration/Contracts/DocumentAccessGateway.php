<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\Contracts;

use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessDecisionV1;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessRequestV1;

interface DocumentAccessGateway
{
    public function decide(DocumentAccessRequestV1 $request): DocumentAccessDecisionV1;
}
