<?php

namespace App\Modules\DocumentManagement\Foundation\Ingestion\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use InvalidArgumentException;

final readonly class IngestDocumentV1
{
    public function __construct(
        public DocumentOwnerContextV1 $ownerContext,
        public UploadIntentV1 $uploadIntent,
        public string $actorReference,
        public mixed $stream,
    ) {
        if (trim($actorReference) === '' || mb_strlen($actorReference) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $actorReference) === 1) {
            throw new InvalidArgumentException('Actor reference must contain 1 through 255 safe characters.');
        }
    }
}
