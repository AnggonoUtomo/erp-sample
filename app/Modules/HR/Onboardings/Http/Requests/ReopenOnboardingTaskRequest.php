<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTaskReasonData;
use Illuminate\Foundation\Http\FormRequest;

class ReopenOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['reopen_reason' => ['required', 'string', 'max:2000']];
    }

    public function toDto(): OnboardingTaskReasonData
    {
        return new OnboardingTaskReasonData((int) $this->user()->id, trim($this->validated('reopen_reason')));
    }
}
