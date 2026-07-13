<?php

namespace App\Modules\DocumentManagement\Foundation\Upload\Contracts;

use App\Modules\DocumentManagement\Foundation\Upload\DTO\DetectedFileTypeV1;

interface FileSignatureDetector
{
    public function inspect(mixed $stream, int $maxBytes): DetectedFileTypeV1;
}
