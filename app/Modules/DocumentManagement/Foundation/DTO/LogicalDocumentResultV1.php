<?php

namespace App\Modules\DocumentManagement\Foundation\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use InvalidArgumentException;

final readonly class LogicalDocumentResultV1
{
    public const STATUSES = ['PENDING'];

    public function __construct(public DocumentReferenceV1 $reference, public string $status)
    {
        if (! in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Unsupported logical document status.');
        }
    }

    /** @return array{schemaVersion: int, reference: string, status: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'reference' => $this->reference->value(),
            'status' => $this->status,
        ];
    }
}
