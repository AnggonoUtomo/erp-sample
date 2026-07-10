<?php

namespace App\Modules\HR\Departements\Http\Requests;

use App\Modules\HR\Departements\DTO\DepartementData;
use App\Modules\HR\Departements\Models\Departement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('departement')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $departementId = $this->route('departement')?->id;

        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', Rule::unique('hr_departements', 'code')->ignore($departementId)],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('hr_departements', 'id'), Rule::notIn([$departementId])],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): DepartementData
    {
        return DepartementData::fromArray($this->validated());
    }
}
