<?php

namespace App\Modules\HR\EmploymentStatuses\Http\Requests;

use App\Modules\HR\EmploymentStatuses\DTO\EmploymentStatusData;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmploymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EmploymentStatus::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_employment_statuses,code'],
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
