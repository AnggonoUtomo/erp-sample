<?php

namespace App\Modules\HR\Departements\DTO;

final readonly class DepartementData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?int $parentId,
        public ?string $description,
        public bool $active,
        public ?int $sortOrder,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: strtoupper((string) $data['code']),
            name: (string) $data['name'],
            parentId: filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null,
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
