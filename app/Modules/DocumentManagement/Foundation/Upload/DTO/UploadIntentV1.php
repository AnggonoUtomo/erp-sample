<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\DTO;

final readonly class UploadIntentV1
{
    public function __construct(
        public string $filename,
        public string $declaredMediaType,
        public int $declaredByteSize,
        public string $idempotencyKey,
    ) {}
}
