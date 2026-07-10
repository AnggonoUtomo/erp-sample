<?php

namespace App\Modules\HR\HRReferenceData\DTO;

final readonly class ReferenceDataData
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $category,
        public string $code,
        public string $name,
        public ?string $description,
        public ?array $metadata,
        public bool $active,
        public ?int $sortOrder,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: str((string) $data['category'])->lower()->kebab()->toString(),
            code: strtoupper((string) $data['code']),
            name: (string) $data['name'],
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            metadata: is_array($data['metadata'] ?? null) ? $data['metadata'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
