<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTaskReasonData;
use Illuminate\Foundation\Http\FormRequest;

class CancelOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('cancel', $this->route('onboarding')) ?? false;
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }

    public function toDto(): OnboardingTaskReasonData
    {
        return new OnboardingTaskReasonData((int) $this->user()->id, $this->validated('reason'));
    }
}
