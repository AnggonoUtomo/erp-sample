<?php

namespace App\Modules\HR\EmploymentStatuses\Http\Requests;

use App\Modules\HR\EmploymentStatuses\DTO\EmploymentStatusData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmploymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('employmentStatus')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employmentStatusId = $this->route('employmentStatus')?->id;

        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', Rule::unique('hr_employment_statuses', 'code')->ignore($employmentStatusId)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'requires_attendance' => ['boolean'],
            'included_in_payroll' => ['boolean'],
            'is_final_status' => ['boolean'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): EmploymentStatusData
    {
        return EmploymentStatusData::fromArray($this->validated());
    }
}
