<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\Contracts;

use App\Modules\DocumentManagement\Foundation\Delivery\DTO\DocumentDeliveryHandoffV1;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\IssueDocumentDeliveryV1;

interface DocumentDeliveryGateway
{
    public function issue(IssueDocumentDeliveryV1 $request): DocumentDeliveryHandoffV1;
}
