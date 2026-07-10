<?php

namespace App\Modules\HR\OrganizationStructures\DTO;

final readonly class OrganizationStructureData
{
    public function __construct(
        public ?int $parentId,
        public ?int $departementId,
        public ?int $positionId,
        public string $code,
        public string $name,
        public string $nodeType,
        public ?string $description,
        public bool $active,
        public ?int $sortOrder,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            parentId: filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null,
            departementId: filled($data['departement_id'] ?? null) ? (int) $data['departement_id'] : null,
            positionId: filled($data['position_id'] ?? null) ? (int) $data['position_id'] : null,
            code: strtoupper((string) $data['code']),
            name: (string) $data['name'],
            nodeType: str((string) ($data['node_type'] ?? 'unit'))->lower()->kebab()->toString(),
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
