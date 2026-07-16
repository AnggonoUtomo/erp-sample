<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\DTO\OffboardingCancellationData;
use Illuminate\Foundation\Http\FormRequest;

class CancelOffboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }

    public function toDto(): OffboardingCancellationData
    {
        return new OffboardingCancellationData(
            (int) $this->user()->id,
            trim($this->validated('reason')),
        );
    }
}
