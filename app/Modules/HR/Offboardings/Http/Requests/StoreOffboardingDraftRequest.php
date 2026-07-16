<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\DTO\OffboardingDraftData;
use App\Modules\HR\Offboardings\Enums\OffboardingExitType;
use App\Modules\HR\Offboardings\Models\Offboarding;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOffboardingDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Offboarding::class) ?? false;
    }

    public function rules(): array
    {
        $employeeId = (int) $this->input('employee_id');

        return [
            'employee_id' => ['required', 'integer', Rule::exists('hr_employees', 'id')->where(fn ($query) => $query->where('active', true)->whereNull('deleted_at'))],
            'employee_contract_id' => ['nullable', 'integer', Rule::exists('hr_employee_contracts', 'id')->where(fn ($query) => $query->where('employee_id', $employeeId)->where('status', 'ACTIVE')->whereNull('deleted_at'))],
            'offboarding_template_id' => ['required', 'integer', Rule::exists('hr_offboarding_templates', 'id')->where(fn ($query) => $query->where('active', true)->whereNull('deleted_at'))],
            'target_employment_status_id' => ['required', 'integer', Rule::exists('hr_employment_statuses', 'id')->where(fn ($query) => $query->where('active', true)->where('is_final_status', true)->whereNull('deleted_at'))],
            'owner_user_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'exit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'exit_type' => ['required', Rule::enum(OffboardingExitType::class)],
            'exit_reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }

    public function toDto(): OffboardingDraftData
    {
        return OffboardingDraftData::fromArray($this->validated());
    }
}
