<?php

namespace App\Modules\HR\Offboardings\DTO;

use App\Modules\HR\Offboardings\Enums\OffboardingExitType;

final readonly class OffboardingDraftData
{
    public function __construct(
        public int $employeeId,
        public ?int $employeeContractId,
        public int $templateId,
        public int $targetEmploymentStatusId,
        public int $ownerUserId,
        public string $exitDate,
        public OffboardingExitType $exitType,
        public string $exitReason,
        public ?string $notes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            employeeId: (int) $data['employee_id'],
            employeeContractId: filled($data['employee_contract_id'] ?? null) ? (int) $data['employee_contract_id'] : null,
            templateId: (int) $data['offboarding_template_id'],
            targetEmploymentStatusId: (int) $data['target_employment_status_id'],
            ownerUserId: (int) $data['owner_user_id'],
            exitDate: (string) $data['exit_date'],
            exitType: OffboardingExitType::from((string) $data['exit_type']),
            exitReason: trim((string) $data['exit_reason']),
            notes: filled($data['notes'] ?? null) ? trim((string) $data['notes']) : null,
        );
    }
}
