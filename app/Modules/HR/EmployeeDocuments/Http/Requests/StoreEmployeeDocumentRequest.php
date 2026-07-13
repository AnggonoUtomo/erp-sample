<?php

namespace App\Modules\HR\EmployeeDocuments\Http\Requests;

use App\Modules\HR\EmployeeDocuments\DTO\EmployeeDocumentData;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Support\EmployeeDocumentTypeCatalog;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreEmployeeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', EmployeeDocument::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', Rule::exists('hr_employees', 'id')->where('active', true)->whereNull('deleted_at')],
            'document_type_id' => ['required', 'integer', Rule::exists('hr_reference_data', 'id')->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('active', true)->whereNull('deleted_at')],
            'document_number' => ['nullable', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date_format:Y-m-d'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:issued_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $type = ReferenceData::query()->find($this->integer('document_type_id'));
            if (($type?->metadata['requires_expiry'] ?? false) && ! $this->filled('expires_at')) {
                $validator->errors()->add('expires_at', 'Tanggal kedaluwarsa wajib untuk tipe dokumen ini.');
            }
            if (($type?->metadata['requires_number'] ?? false) && ! $this->filled('document_number')) {
                $validator->errors()->add('document_number', 'Nomor dokumen wajib untuk tipe dokumen ini.');
            }
        }];
    }

    public function toDto(): EmployeeDocumentData
    {
        return EmployeeDocumentData::fromArray($this->validated());
    }
}
