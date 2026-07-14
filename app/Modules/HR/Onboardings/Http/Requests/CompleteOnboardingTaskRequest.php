<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use App\Modules\HR\Onboardings\DTO\OnboardingTaskCompletionData;
use Illuminate\Foundation\Http\FormRequest;

class CompleteOnboardingTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['completion_note' => ['nullable', 'string', 'max:2000']];
    }

    public function toDto(): OnboardingTaskCompletionData
    {
        $note = $this->validated('completion_note');

        return new OnboardingTaskCompletionData((int) $this->user()->id, $note === null ? null : trim($note));
    }
}
