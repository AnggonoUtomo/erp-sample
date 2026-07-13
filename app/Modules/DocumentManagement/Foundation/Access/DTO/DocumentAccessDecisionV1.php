<?php

namespace App\Modules\DocumentManagement\Foundation\Access\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use InvalidArgumentException;

final readonly class DocumentAccessDecisionV1
{
    public function __construct(
        public DocumentReferenceV1 $reference,
        public string $action,
        public string $state,
    ) {
        if (preg_match('/^[A-Z_]{1,32}$/', $action) !== 1) {
            throw new InvalidArgumentException('Access decision action is invalid.');
        }
        if (! in_array($state, DocumentReferenceDescriptorV1::STATES, true)) {
            throw new InvalidArgumentException('Access decision state is invalid.');
        }
    }

    /** @return array{schemaVersion: int, reference: string, action: string, state: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'reference' => $this->reference->value(),
            'action' => $this->action,
            'state' => $this->state,
        ];
    }
}
