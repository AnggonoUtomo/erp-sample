<?php

namespace App\Modules\HR\HRReferenceData\Http\Requests;

use App\Modules\HR\HRReferenceData\DTO\ReferenceCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReferenceCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => str((string) ($this->input('code') ?: $this->input('name')))->lower()->kebab()->toString(),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['hr-reference-data.update', 'hr-reference-data.manage']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $categoryId = $this->route('referenceCategory')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:64', 'alpha_dash:ascii', Rule::unique('hr_reference_categories', 'code')->ignore($categoryId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): ReferenceCategoryData
    {
        return ReferenceCategoryData::fromArray($this->validated());
    }
}
