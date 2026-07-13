<?php

namespace App\Modules\DocumentManagement\Foundation\Versioning\DTO;

use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use InvalidArgumentException;

final readonly class ReplaceDocumentVersionV1
{
    public DocumentReferenceV1 $reference;

    public function __construct(
        string $reference,
        public UploadIntentV1 $uploadIntent,
        public string $actorReference,
        public mixed $stream,
    ) {
        $this->reference = new DocumentReferenceV1($reference);
        if (trim($actorReference) === '' || mb_strlen($actorReference) > 255
            || preg_match('/[\x00-\x1F\x7F]/', $actorReference) === 1) {
            throw new InvalidArgumentException('Actor reference must contain 1 through 255 safe characters.');
        }
    }
}
