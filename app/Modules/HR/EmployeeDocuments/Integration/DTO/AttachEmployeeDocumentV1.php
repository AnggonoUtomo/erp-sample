<?php

namespace App\Modules\HR\EmployeeDocuments\Integration\DTO;

use InvalidArgumentException;

final readonly class AttachEmployeeDocumentV1
{
    public function __construct(
        public int $employeeDocumentId,
        public string $filename,
        public string $declaredMediaType,
        public int $declaredByteSize,
        public string $idempotencyKey,
        public string $actorReference,
        public mixed $stream,
    ) {
        if ($employeeDocumentId < 1) {
            throw new InvalidArgumentException('Employee document ID must be positive.');
        }
        if (trim($actorReference) === '' || mb_strlen($actorReference) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $actorReference) === 1) {
            throw new InvalidArgumentException('Actor reference must contain 1 through 255 safe characters.');
        }
        $mode = is_resource($stream) ? (string) (stream_get_meta_data($stream)['mode'] ?? '') : '';
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream'
            || (! str_contains($mode, 'r') && ! str_contains($mode, '+'))) {
            throw new InvalidArgumentException('Attachment stream must be a readable resource.');
        }
    }
}
