<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.restore') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
