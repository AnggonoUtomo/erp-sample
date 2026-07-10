<?php

namespace App\Modules\HR\EmploymentTypes\Http\Requests;

use App\Modules\HR\EmploymentTypes\DTO\EmploymentTypeData;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmploymentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EmploymentType::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_employment_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'requires_contract_end_date' => ['boolean'],
            'included_in_payroll' => ['boolean'],
            'eligible_for_benefits' => ['boolean'],
            'eligible_for_overtime' => ['boolean'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): EmploymentTypeData
    {
        return EmploymentTypeData::fromArray($this->validated());
    }
}
