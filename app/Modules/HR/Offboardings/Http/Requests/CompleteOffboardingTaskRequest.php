<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\DTO\OffboardingTaskCompletionData;
use Illuminate\Foundation\Http\FormRequest;

class CompleteOffboardingTaskRequest extends FormRequest
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

    public function toDto(): OffboardingTaskCompletionData
    {
        $note = $this->validated('completion_note');

        return new OffboardingTaskCompletionData((int) $this->user()->id, $note === null ? null : trim($note));
    }
}
