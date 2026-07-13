<?php

namespace App\Modules\DocumentManagement\Foundation\Storage\DTO;

use InvalidArgumentException;

final readonly class StorageObjectKeyV1
{
    public function __construct(private string $key)
    {
        if (! preg_match('#^(staged|objects)/[0-9A-HJKMNP-TV-Z]{26}$#', $key)) {
            throw new InvalidArgumentException('Storage object key is invalid.');
        }
    }

    public function value(): string
    {
        return $this->key;
    }

    public function isStaged(): bool
    {
        return str_starts_with($this->key, 'staged/');
    }
}
