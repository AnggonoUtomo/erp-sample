<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\DTO;

use InvalidArgumentException;

final readonly class DocumentReferenceDescriptorV1
{
    public const STATES = ['AVAILABLE', 'MISSING', 'ARCHIVED', 'UNAVAILABLE', 'DENIED'];

    public function __construct(public DocumentReferenceV1 $reference, public string $state)
    {
        if (! in_array($state, self::STATES, true)) {
            throw new InvalidArgumentException('Unsupported document reference state.');
        }
    }

    /** @return array{schemaVersion: int, reference: string, state: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'reference' => $this->reference->value(),
            'state' => $this->state,
        ];
    }
}
