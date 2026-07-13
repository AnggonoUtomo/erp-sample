<?php

namespace App\Modules\HR\EmployeeContracts\Http\Requests;

use App\Modules\HR\EmployeeContracts\DTO\EmployeeContractData;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EmployeeContract::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:hr_employees,id'],
            'employment_type_id' => ['required', 'integer', 'exists:hr_employment_types,id'],
            'contract_number' => ['required', 'string', 'max:100', 'unique:hr_employee_contracts,contract_number'],
            'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'signed_date' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $type = EmploymentType::query()->find($this->integer('employment_type_id'));
            if ($type?->requires_contract_end_date && ! $this->input('end_date')) {
                $validator->errors()->add('end_date', 'Tanggal akhir wajib untuk tipe kontrak ini.');

                return;
            }

            $start = $this->date('start_date')->toDateString();
            $end = $this->input('end_date');
            $overlaps = EmployeeContract::query()->where('employee_id', $this->integer('employee_id'))
                ->overlapping($start, $end)->exists();
            if ($overlaps) {
                $validator->errors()->add('start_date', 'Periode kontrak overlap dengan kontrak lain.');
            }
        }];
    }

    public function toDto(): EmployeeContractData
    {
        return EmployeeContractData::fromArray($this->validated());
    }
}
