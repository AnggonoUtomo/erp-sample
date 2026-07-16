<?php

namespace App\Modules\HR\Offboardings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShowOffboardingRequest extends FormRequest
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
