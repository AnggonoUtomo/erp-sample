<?php

namespace App\Modules\HR\Onboardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return ['business_date' => ['required', 'date_format:Y-m-d']];
    }
}
