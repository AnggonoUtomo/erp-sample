<?php

namespace App\Modules\DocumentManagement\Foundation\Delivery\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use InvalidArgumentException;

final readonly class IssueDocumentDeliveryV1
{
    public string $action;

    public function __construct(
        public DocumentReferenceV1 $reference,
        public string $actorReference,
        string $action,
        public DocumentOwnerContextV1 $expectedOwner,
    ) {
        if (preg_match('/^user:[1-9][0-9]*$/', $actorReference) !== 1) {
            throw new InvalidArgumentException('Delivery actor reference must be canonical.');
        }
        $action = strtoupper(trim($action));
        if ($action !== 'DOWNLOAD') {
            throw new InvalidArgumentException('Delivery action must be DOWNLOAD.');
        }
        $this->action = $action;
    }
}
