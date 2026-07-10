<?php

namespace App\Modules\HR\HRReferenceData\Http\Requests;

use App\Modules\HR\HRReferenceData\DTO\ReferenceDataData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReferenceDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('referenceData')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $referenceDataId = $this->route('referenceData')?->id;
        $category = str((string) $this->input('category'))->lower()->kebab()->toString();

        return [
            'category' => ['required', 'string', 'max:64', 'alpha_dash:ascii', Rule::exists('hr_reference_categories', 'code')->where('active', true)->whereNull('deleted_at')],
            'code' => [
                'required',
                'string',
                'max:64',
                'alpha_dash:ascii',
                Rule::unique('hr_reference_data', 'code')
                    ->where(fn ($query) => $query->where('category', $category))
                    ->ignore($referenceDataId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'metadata' => ['nullable', 'array'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): ReferenceDataData
    {
        return ReferenceDataData::fromArray($this->validated());
    }
}
