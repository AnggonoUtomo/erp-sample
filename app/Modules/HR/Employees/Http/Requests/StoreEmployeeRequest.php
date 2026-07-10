<?php

namespace App\Modules\HR\Employees\Http\Requests;

use App\Modules\HR\Employees\DTO\EmployeeData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['employees.create', 'employees.manage']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'unique:hr_employees,user_id'],
            'departement_id' => ['nullable', 'integer', Rule::exists('hr_departements', 'id')->where('active', true)->whereNull('deleted_at')],
            'position_id' => ['nullable', 'integer', Rule::exists('hr_positions', 'id')->where('active', true)->whereNull('deleted_at')],
            'job_level_id' => ['nullable', 'integer', Rule::exists('hr_job_levels', 'id')->where('active', true)->whereNull('deleted_at')],
            'work_location_id' => ['nullable', 'integer', Rule::exists('hr_work_locations', 'id')->where('active', true)->whereNull('deleted_at')],
            'employment_status_id' => ['required', 'integer', Rule::exists('hr_employment_statuses', 'id')->where('active', true)->whereNull('deleted_at')],
            'employment_type_id' => ['nullable', 'integer', Rule::exists('hr_employment_types', 'id')->where('active', true)->whereNull('deleted_at')],
            'employee_number' => ['required', 'string', 'max:64', 'alpha_dash:ascii', 'unique:hr_employees,employee_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:160'],
            'work_email' => ['nullable', 'email', 'max:255', 'unique:hr_employees,work_email'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'hired_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:hired_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'remove_avatar' => ['boolean'],
        ];
    }

    public function toDto(): EmployeeData
    {
        return EmployeeData::fromArray($this->validated(), $this->file('avatar'));
    }
}
