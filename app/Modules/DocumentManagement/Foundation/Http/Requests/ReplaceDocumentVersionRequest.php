<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use App\Modules\DocumentManagement\Foundation\Versioning\DTO\ReplaceDocumentVersionV1;
use Illuminate\Foundation\Http\FormRequest;
use RuntimeException;

class ReplaceDocumentVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.replace') ?? false;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'string', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
            'file' => ['required', 'file', 'max:'.intdiv((int) config('document-management.upload.max_bytes'), 1024)],
        ];
    }

    public function toDto(string $reference): ReplaceDocumentVersionV1
    {
        $file = $this->file('file');
        $stream = $file === null ? false : fopen($file->getRealPath(), 'rb');
        if ($file === null || $stream === false) {
            throw new RuntimeException('Unable to open uploaded file stream.');
        }

        return new ReplaceDocumentVersionV1(
            $reference,
            new UploadIntentV1(
                $file->getClientOriginalName(),
                (string) $file->getClientMimeType(),
                (int) $file->getSize(),
                $this->string('idempotency_key')->toString(),
            ),
            'user:'.$this->user()->getAuthIdentifier(),
            $stream,
        );
    }
}
