<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsumeDocumentDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['token' => ['required', 'string', 'size:43', 'regex:/^[A-Za-z0-9_-]+$/']];
    }
}
