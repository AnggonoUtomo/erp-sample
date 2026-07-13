<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchiveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.archive') ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000', 'not_regex:/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/'],
        ];
    }
}
