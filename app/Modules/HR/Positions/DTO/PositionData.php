<?php

namespace App\Modules\HR\Positions\DTO;

final readonly class PositionData
{
    public function __construct(
        public int $departementId,
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
            departementId: (int) $data['departement_id'],
            code: strtoupper((string) $data['code']),
            name: (string) $data['name'],
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
