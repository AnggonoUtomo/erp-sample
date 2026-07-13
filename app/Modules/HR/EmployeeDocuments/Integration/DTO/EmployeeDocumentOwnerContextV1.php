<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\DTO;

use InvalidArgumentException;

final readonly class EmployeeDocumentOwnerContextV1
{
    private function __construct(public string $aggregateId) {}

    public static function forDocument(int $documentId): self
    {
        if ($documentId < 1) {
            throw new InvalidArgumentException('Employee document owner ID must be positive.');
        }

        return new self((string) $documentId);
    }

    /** @return array{schemaVersion: int, domain: string, aggregateType: string, aggregateId: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'domain' => 'HR',
            'aggregateType' => 'EmployeeDocument',
            'aggregateId' => $this->aggregateId,
        ];
    }
}
