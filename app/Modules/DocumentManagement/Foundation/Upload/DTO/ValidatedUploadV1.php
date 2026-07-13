<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\DTO;

final readonly class ValidatedUploadV1
{
    public function __construct(
        public string $filename,
        public string $extension,
        public string $declaredMediaType,
        public string $detectedMediaType,
        public int $byteSize,
        public string $scanStatus,
    ) {}

    /** @return array{schemaVersion: int, filename: string, extension: string, declaredMediaType: string, detectedMediaType: string, byteSize: int, scanStatus: string} */
    public function toArray(): array
    {
        return [
            'schemaVersion' => 1,
            'filename' => $this->filename,
            'extension' => $this->extension,
            'declaredMediaType' => $this->declaredMediaType,
            'detectedMediaType' => $this->detectedMediaType,
            'byteSize' => $this->byteSize,
            'scanStatus' => $this->scanStatus,
        ];
    }
}
