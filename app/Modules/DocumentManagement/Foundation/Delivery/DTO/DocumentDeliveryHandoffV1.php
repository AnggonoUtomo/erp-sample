<?php

namespace App\Modules\DocumentManagement\Foundation\Delivery\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use Carbon\CarbonImmutable;

final readonly class DocumentDeliveryHandoffV1
{
    public int $schemaVersion;

    public function __construct(
        public DocumentReferenceV1 $reference,
        public string $action,
        public string $token,
        public CarbonImmutable $expiresAt,
    ) {
        $this->schemaVersion = 1;
    }

    /** @return array{schemaVersion: int, reference: string, action: string, token: string, expiresAt: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'reference' => $this->reference->value(),
            'action' => $this->action,
            'token' => $this->token,
            'expiresAt' => $this->expiresAt->toIso8601String(),
        ];
    }
}
