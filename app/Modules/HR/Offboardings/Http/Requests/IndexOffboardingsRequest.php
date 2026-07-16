<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\Enums\OffboardingExitType;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOffboardingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:hr_employees,id'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'template_id' => ['nullable', 'integer', 'exists:hr_offboarding_templates,id'],
            'status' => ['nullable', Rule::enum(OffboardingStatus::class)],
            'exit_type' => ['nullable', Rule::enum(OffboardingExitType::class)],
            'exit_from' => ['nullable', 'date_format:Y-m-d'],
            'exit_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:exit_from'],
            'due' => ['nullable', 'boolean'],
            'overdue' => ['nullable', 'boolean'],
            'archived' => ['nullable', 'boolean'],
            'business_date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        $validated = $this->validated();
        $validated['due'] = $this->boolean('due');
        $validated['overdue'] = $this->boolean('overdue');
        $validated['archived'] = $this->boolean('archived');
        $validated['business_date'] = $validated['business_date'] ?? now()->toDateString();

        return $validated;
    }
}
