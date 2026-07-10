<?php

namespace App\Modules\HR\EmploymentTypes\DTO;

final readonly class EmploymentTypeData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public bool $requiresContractEndDate,
        public bool $includedInPayroll,
        public bool $eligibleForBenefits,
        public bool $eligibleForOvertime,
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
            requiresContractEndDate: (bool) ($data['requires_contract_end_date'] ?? false),
            includedInPayroll: (bool) ($data['included_in_payroll'] ?? true),
            eligibleForBenefits: (bool) ($data['eligible_for_benefits'] ?? true),
            eligibleForOvertime: (bool) ($data['eligible_for_overtime'] ?? true),
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
