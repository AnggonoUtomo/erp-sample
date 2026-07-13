<?php

namespace App\Modules\DocumentManagement\Foundation\Access\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use InvalidArgumentException;

final readonly class DocumentAccessRequestV1
{
    public string $action;

    public function __construct(
        public DocumentReferenceV1 $reference,
        public ?string $actorReference,
        string $action,
        public DocumentOwnerContextV1 $expectedOwner,
    ) {
        if ($actorReference !== null && (mb_strlen($actorReference) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $actorReference) === 1)) {
            throw new InvalidArgumentException('Actor reference is invalid.');
        }

        $action = strtoupper(trim($action));
        if ($action === '' || mb_strlen($action) > 32 || preg_match('/^[A-Z_]+$/', $action) !== 1) {
            throw new InvalidArgumentException('Access action is invalid.');
        }
        $this->action = $action;
    }
}
