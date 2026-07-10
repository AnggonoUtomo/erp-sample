<?php

namespace App\Modules\HR\EmploymentStatuses\DTO;

final readonly class EmploymentStatusData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public bool $requiresAttendance,
        public bool $includedInPayroll,
        public bool $isFinalStatus,
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
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            requiresAttendance: (bool) ($data['requires_attendance'] ?? true),
            includedInPayroll: (bool) ($data['included_in_payroll'] ?? true),
            isFinalStatus: (bool) ($data['is_final_status'] ?? false),
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
