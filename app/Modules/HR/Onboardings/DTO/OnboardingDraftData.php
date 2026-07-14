<?php

namespace App\Modules\HR\Onboardings\DTO;

final readonly class OnboardingDraftData
{
    public function __construct(public int $employeeId, public ?int $employeeContractId, public int $templateId, public int $ownerUserId, public string $startDate) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((int) $data['employee_id'], filled($data['employee_contract_id'] ?? null) ? (int) $data['employee_contract_id'] : null, (int) $data['onboarding_template_id'], (int) $data['owner_user_id'], (string) $data['start_date']);
    }
}
