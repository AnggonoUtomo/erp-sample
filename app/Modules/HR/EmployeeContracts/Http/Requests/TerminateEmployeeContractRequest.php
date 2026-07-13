<?php

namespace App\Modules\HR\EmployeeContracts\Http\Requests;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TerminateEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('terminate', $this->route('employeeContract')) ?? false;
    }

    public function rules(): array
    {
        return ['end_date' => ['required', 'date'], 'reason' => ['required', 'string', 'max:500']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('end_date')) {
                return;
            }

            /** @var EmployeeContract $contract */
            $contract = $this->route('employeeContract');
            $endDate = $this->date('end_date');
            if ($endDate->lt($contract->start_date) || ($contract->end_date && $endDate->gt($contract->end_date))) {
                $validator->errors()->add('end_date', 'Tanggal akhir harus berada dalam periode contract.');
            }
        }];
    }
}
