<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTaskReasonData;
use Illuminate\Foundation\Http\FormRequest;

class SkipOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['skip_reason' => ['required', 'string', 'max:2000']];
    }

    public function toDto(): OnboardingTaskReasonData
    {
        return new OnboardingTaskReasonData((int) $this->user()->id, trim($this->validated('skip_reason')));
    }
}
