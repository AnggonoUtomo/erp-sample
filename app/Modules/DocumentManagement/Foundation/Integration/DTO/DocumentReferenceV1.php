<?php

namespace App\Modules\DocumentManagement\Foundation\Integration\DTO;

use InvalidArgumentException;

final readonly class DocumentReferenceV1
{
    public function __construct(private string $reference)
    {
        if ($reference === '' || mb_strlen($reference) > 255 || preg_match('/[\x00-\x1F\x7F]/', $reference)) {
            throw new InvalidArgumentException('Document reference must be a valid opaque value of at most 255 characters.');
        }
    }

    public function value(): string
    {
        return $this->reference;
    }
}
