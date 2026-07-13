<?php

namespace App\Modules\HR\EmployeeContracts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelEmployeeContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancel', $this->route('employeeContract')) ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:500']];
    }
}
