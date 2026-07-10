<?php

namespace App\Modules\HR\Departements\Http\Requests;

use App\Modules\HR\Departements\DTO\DepartementData;
use App\Modules\HR\Departements\Models\Departement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Departement::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_departements,code'],
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', Rule::exists('hr_departements', 'id')],
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
