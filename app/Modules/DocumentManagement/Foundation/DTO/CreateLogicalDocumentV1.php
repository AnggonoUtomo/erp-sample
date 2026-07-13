<?php

namespace App\Modules\DocumentManagement\Foundation\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use InvalidArgumentException;

final readonly class CreateLogicalDocumentV1
{
    public function __construct(
        public DocumentOwnerContextV1 $ownerContext,
        public string $idempotencyKey,
        public string $actorReference,
    ) {
        if (trim($idempotencyKey) === '' || mb_strlen($idempotencyKey) > 255 || $this->hasControlCharacters($idempotencyKey)) {
            throw new InvalidArgumentException('Idempotency key must contain 1 through 255 safe characters.');
        }

        if (trim($actorReference) === '' || mb_strlen($actorReference) > 255 || $this->hasControlCharacters($actorReference)) {
            throw new InvalidArgumentException('Actor reference must contain 1 through 255 safe characters.');
        }
    }

    private function hasControlCharacters(string $value): bool
    {
        return preg_match('/[\x00-\x1F\x7F]/', $value) === 1;
    }
}
