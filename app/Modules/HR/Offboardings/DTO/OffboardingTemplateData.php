<?php

namespace App\Modules\HR\Offboardings\DTO;

final readonly class OffboardingTemplateData
{
    /** @param list<array{title: string, description: ?string, category: string, required: bool, due_offset_days: int, default_assignee_role: ?string}> $items */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public bool $active,
        public array $items,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: strtoupper(trim((string) $data['code'])),
            name: trim((string) $data['name']),
            description: filled($data['description'] ?? null)
                ? trim((string) $data['description'])
                : null,
            active: (bool) ($data['active'] ?? true),
            items: array_map(fn (array $item) => [
                'title' => trim((string) $item['title']),
                'description' => filled($item['description'] ?? null)
                    ? trim((string) $item['description'])
                    : null,
                'category' => strtoupper(trim((string) $item['category'])),
                'required' => (bool) ($item['required'] ?? true),
                'due_offset_days' => (int) ($item['due_offset_days'] ?? 0),
                'default_assignee_role' => filled($item['default_assignee_role'] ?? null)
                    ? trim((string) $item['default_assignee_role'])
                    : null,
            ], $data['items']),
        );
    }
}
