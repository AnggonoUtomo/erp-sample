<?php

namespace App\Modules\HR\OrganizationStructures\Http\Requests;

use App\Modules\HR\OrganizationStructures\DTO\OrganizationStructureData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('organizationStructure')) ?? false;
    }

    public function rules(): array
    {
        $organizationStructureId = $this->route('organizationStructure')?->id;

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('hr_organization_structures', 'id')->whereNull('deleted_at'), Rule::notIn([$organizationStructureId])],
            'departement_id' => ['nullable', 'integer', Rule::exists('hr_departements', 'id')->where('active', true)->whereNull('deleted_at')],
            'position_id' => ['nullable', 'integer', Rule::exists('hr_positions', 'id')->where('active', true)->whereNull('deleted_at')],
            'code' => ['required', 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('hr_organization_structures', 'code')->ignore($organizationStructureId)],
            'name' => ['required', 'string', 'max:255'],
            'node_type' => ['required', 'string', 'max:32', Rule::in(['company', 'division', 'departement', 'unit', 'team', 'position'])],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): OrganizationStructureData
    {
        return OrganizationStructureData::fromArray($this->validated());
    }
}
