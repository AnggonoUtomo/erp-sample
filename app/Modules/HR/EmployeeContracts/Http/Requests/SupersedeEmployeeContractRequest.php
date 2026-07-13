<?php

namespace App\Modules\HR\EmployeeContracts\Http\Requests;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SupersedeEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('supersede', $this->route('employeeContract')) ?? false;
    }

    public function rules(): array
    {
        return [
            'employment_type_id' => ['required', 'integer', 'exists:hr_employment_types,id'],
            'contract_number' => ['required', 'string', 'max:100', 'unique:hr_employee_contracts,contract_number'],
            'start_date' => ['required', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'signed_date' => ['nullable', 'date'], 'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var EmployeeContract $old */
            $old = $this->route('employeeContract');
            if ($this->date('start_date')->lte($old->start_date)) {
                $validator->errors()->add('start_date', 'Replacement harus dimulai setelah contract lama.');
            }

            $type = EmploymentType::query()->find($this->integer('employment_type_id'));
            if ($type?->requires_contract_end_date && ! $this->input('end_date')) {
                $validator->errors()->add('end_date', 'Tanggal akhir wajib untuk tipe kontrak ini.');
            }
        }];
    }
}
