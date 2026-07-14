<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTemplateData;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Http\FormRequest;

class StoreOnboardingTemplateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('create', OnboardingTemplate::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_onboarding_templates,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:2000'],
            'items.*.category' => ['required', 'string', 'max:64'],
            'items.*.required' => ['boolean'],
            'items.*.due_offset_days' => ['required', 'integer', 'between:-365,365'],
            'items.*.default_assignee_role' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDto(): OnboardingTemplateData
    {
        return OnboardingTemplateData::fromArray($this->validated());
    }
}
