<?php

namespace App\Modules\HR\EmployeeMovements\Http\Requests;

use App\Modules\HR\EmployeeMovements\DTO\EmployeeMovementData;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EmployeeMovement::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:hr_employees,id'],
            'type' => ['nullable', 'string', Rule::in(['TRANSFER', 'PROMOTION', 'DEMOTION', 'EMPLOYMENT_CHANGE'])],
            'effective_date' => ['required', 'date_format:Y-m-d'],
            'departement_id' => ['nullable', 'integer', 'exists:hr_departements,id'],
            'position_id' => ['nullable', 'integer', 'exists:hr_positions,id'],
            'job_level_id' => ['nullable', 'integer', Rule::exists('hr_job_levels', 'id')->where('active', true)->whereNull('deleted_at')],
            'employment_status_id' => ['nullable', 'integer', Rule::exists('hr_employment_statuses', 'id')->where('active', true)->whereNull('deleted_at')],
            'employment_type_id' => ['nullable', 'integer', Rule::exists('hr_employment_types', 'id')->where('active', true)->whereNull('deleted_at')],
            'work_location_id' => ['nullable', 'integer', 'exists:hr_work_locations,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:hr_employees,id'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            if ($this->input('effective_date') !== now()->toDateString()) {
                $validator->errors()->add('effective_date', 'Slice ini hanya menerapkan movement yang efektif hari ini.');
            }
            $employee = Employee::query()->find($this->integer('employee_id'));
            if (! $employee) {
                return;
            }
            $type = (string) ($this->input('type') ?: 'TRANSFER');
            if (in_array($type, ['PROMOTION', 'DEMOTION'], true)) {
                if (! $this->filled('job_level_id')) {
                    $validator->errors()->add('job_level_id', 'Promotion/demotion wajib memilih job level tujuan.');
                } elseif ($this->integer('job_level_id') === $employee->job_level_id) {
                    $validator->errors()->add('job_level_id', 'Promotion/demotion wajib mengubah job level.');
                }
            }
            if ($type === 'EMPLOYMENT_CHANGE'
                && ! $this->filled('employment_status_id')
                && ! $this->filled('employment_type_id')) {
                $validator->errors()->add('employment', 'Employment change wajib mengubah status atau type employment.');
            }
            if ($this->filled('supervisor_id') && $this->integer('supervisor_id') === $employee->id) {
                $validator->errors()->add('supervisor_id', 'Employee tidak boleh menjadi supervisor dirinya sendiri.');
            }
            $departmentId = $this->filled('departement_id') ? $this->integer('departement_id') : $employee->departement_id;
            $positionId = $this->filled('position_id') ? $this->integer('position_id') : $employee->position_id;
            if ($positionId && Position::query()->whereKey($positionId)->where('departement_id', $departmentId)->doesntExist()) {
                $validator->errors()->add('position_id', 'Position harus berada pada department tujuan.');
            }
        }];
    }

    public function toDto(): EmployeeMovementData
    {
        return EmployeeMovementData::fromArray($this->validated());
    }
}
