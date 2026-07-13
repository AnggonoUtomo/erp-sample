<?php

namespace App\Modules\DocumentManagement\Foundation\Delivery\DTO;

final readonly class DocumentDeliveryPayloadV1
{
    public function __construct(
        public mixed $stream,
        public string $filename,
        public string $mediaType,
        public int $byteSize,
    ) {}
}
