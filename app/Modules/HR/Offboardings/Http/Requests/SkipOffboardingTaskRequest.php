<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\DTO\OffboardingTaskReasonData;
use Illuminate\Foundation\Http\FormRequest;

class SkipOffboardingTaskRequest extends FormRequest
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

    public function toDto(): OffboardingTaskReasonData
    {
        return new OffboardingTaskReasonData(
            (int) $this->user()->id,
            trim($this->validated('skip_reason')),
        );
    }
}
