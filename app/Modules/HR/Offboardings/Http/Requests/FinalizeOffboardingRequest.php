<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use App\Modules\HR\Offboardings\DTO\OffboardingFinalizationData;
use Illuminate\Foundation\Http\FormRequest;

class FinalizeOffboardingRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'business_date' => ['required', 'date_format:Y-m-d'],
        ];
    }

    public function toDto(): OffboardingFinalizationData
    {
        return new OffboardingFinalizationData(
            businessDate: $this->validated('business_date'),
            actorUserId: (int) $this->user()->getAuthIdentifier(),
        );
    }
}
