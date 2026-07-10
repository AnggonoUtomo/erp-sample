<?php

namespace App\Modules\HR\HRReferenceData\DTO;

final readonly class ReferenceCategoryData
{
    public function __construct(
        public string $code,
        public string $name,
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
            code: str((string) ($data['code'] ?? $data['name']))->lower()->kebab()->toString(),
            name: (string) $data['name'],
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
