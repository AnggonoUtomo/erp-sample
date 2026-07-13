<?php

namespace App\Modules\HR\EmployeeDocuments\Http\Requests;

use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use Illuminate\Foundation\Http\FormRequest;

class EmployeeDocumentAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('employeeDocument');

        return $document instanceof EmployeeDocument && ($this->user()?->can('attach', $document) ?? false);
    }

    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'max:20480', 'extensions:pdf,jpg,jpeg,png'],
            'idempotency_key' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._:-]+$/'],
        ];
    }

    public function toDto(EmployeeDocument $document): AttachEmployeeDocumentV1
    {
        $file = $this->file('attachment');
        $stream = $file === null ? false : fopen($file->getRealPath(), 'rb');

        return new AttachEmployeeDocumentV1(
            $document->getKey(),
            $file?->getClientOriginalName() ?? '',
            $file?->getClientMimeType() ?? '',
            (int) ($file?->getSize() ?? 0),
            $this->string('idempotency_key')->toString(),
            'user:'.$this->user()->getAuthIdentifier(),
            $stream,
        );
    }
}
