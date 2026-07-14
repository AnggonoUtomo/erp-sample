<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOnboardingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:hr_employees,id'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'template_id' => ['nullable', 'integer', 'exists:hr_onboarding_templates,id'],
            'status' => ['nullable', Rule::enum(OnboardingStatus::class)],
            'start_from' => ['nullable', 'date_format:Y-m-d'],
            'start_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_from'],
            'overdue' => ['nullable', 'boolean'],
            'archived' => ['nullable', 'boolean'],
            'business_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        $validated = $this->validated();
        $validated['overdue'] = $this->boolean('overdue');
        $validated['archived'] = $this->boolean('archived');
        $validated['business_date'] = $validated['business_date'] ?? now()->toDateString();

        return $validated;
    }
}
