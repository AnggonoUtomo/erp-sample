<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingDraftData;
use App\Modules\HR\Onboardings\Models\Onboarding;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOnboardingDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Onboarding::class) ?? false;
    }

    public function rules(): array
    {
        $employeeId = (int) $this->input('employee_id');

        return [
            'employee_id' => ['required', 'integer', Rule::exists('hr_employees', 'id')->where(fn ($query) => $query->where('active', true)->whereNull('deleted_at'))],
            'employee_contract_id' => ['nullable', 'integer', Rule::exists('hr_employee_contracts', 'id')->where(fn ($query) => $query->where('employee_id', $employeeId)->where('status', '!=', 'CANCELLED')->whereNull('deleted_at'))],
            'onboarding_template_id' => ['required', 'integer', Rule::exists('hr_onboarding_templates', 'id')->where(fn ($query) => $query->where('active', true)->whereNull('deleted_at'))],
            'owner_user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'start_date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function toDto(): OnboardingDraftData
    {
        return OnboardingDraftData::fromArray($this->validated());
    }
}
