<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\DTO;

use InvalidArgumentException;

final readonly class DocumentOwnerContextV1
{
    public function __construct(
        public string $domain,
        public string $aggregateType,
        public string $aggregateId,
    ) {
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,63}$/', $domain)) {
            throw new InvalidArgumentException('Document owner domain is invalid.');
        }

        if (! preg_match('/^[A-Za-z][A-Za-z0-9_-]{0,127}$/', $aggregateType)) {
            throw new InvalidArgumentException('Document owner aggregate type is invalid.');
        }

        if ($aggregateId === '' || mb_strlen($aggregateId) > 255 || preg_match('/[\x00-\x1F\x7F]/', $aggregateId)) {
            throw new InvalidArgumentException('Document owner aggregate ID is invalid.');
        }
    }

    /** @return array{schemaVersion: int, domain: string, aggregateType: string, aggregateId: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'domain' => $this->domain,
            'aggregateType' => $this->aggregateType,
            'aggregateId' => $this->aggregateId,
        ];
    }
}
