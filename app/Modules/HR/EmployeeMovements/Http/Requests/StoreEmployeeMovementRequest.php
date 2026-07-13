<?php

namespace App\Modules\HR\EmployeeMovements\Http\Requests;

use App\Modules\HR\EmployeeMovements\DTO\EmployeeMovementData;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
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
            'effective_date' => ['required', 'date_format:Y-m-d'],
            'departement_id' => ['nullable', 'integer', 'exists:hr_departements,id'],
            'position_id' => ['nullable', 'integer', 'exists:hr_positions,id'],
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
