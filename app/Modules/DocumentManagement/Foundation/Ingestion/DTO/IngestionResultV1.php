<?php

namespace App\Modules\DocumentManagement\Foundation\Ingestion\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;

final readonly class IngestionResultV1
{
    public function __construct(
        public DocumentReferenceV1 $reference,
        public int $version,
        public string $status,
        public string $scanStatus,
    ) {}

    /** @return array{schemaVersion: int, reference: string, version: int, status: string, scanStatus: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'reference' => $this->reference->value(),
            'version' => $this->version,
            'status' => $this->status,
            'scanStatus' => $this->scanStatus,
        ];
    }
}
