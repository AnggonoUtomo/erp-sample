<?php

namespace App\Modules\Console\AccessControls\DTO;

final readonly class PermissionData
{
    public function __construct(
        public string $name,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(name: $data['name']);
    }
}
