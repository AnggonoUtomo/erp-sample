<?php

namespace App\Modules\HR\EmployeeDocuments\Http\Requests;

use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeDocumentVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('employeeDocument');

        return $document instanceof EmployeeDocument && $this->user()?->can('verify', $document) === true;
    }

    public function rules(): array
    {
        $required = $this->routeIs('hr.employee-documents.reject') ? 'required' : 'nullable';

        return ['reason' => [$required, 'string', 'max:1000']];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('reason')) {
            $this->merge(['reason' => trim((string) $this->input('reason'))]);
        }
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason');

        return is_string($reason) && $reason !== '' ? $reason : null;
    }
}
