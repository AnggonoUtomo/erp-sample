<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\DTO;

final readonly class DetectedFileTypeV1
{
    public function __construct(
        public string $canonicalExtension,
        public string $mediaType,
        public int $byteSize,
    ) {}
}
