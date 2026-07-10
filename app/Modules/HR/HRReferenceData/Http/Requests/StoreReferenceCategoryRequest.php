<?php

namespace App\Modules\HR\HRReferenceData\Http\Requests;

use App\Modules\HR\HRReferenceData\DTO\ReferenceCategoryData;
use Illuminate\Foundation\Http\FormRequest;

class StoreReferenceCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => str((string) ($this->input('code') ?: $this->input('name')))->lower()->kebab()->toString(),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasAnyPermission(['hr-reference-data.create', 'hr-reference-data.manage']) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:64', 'alpha_dash:ascii', 'unique:hr_reference_categories,code'],
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
