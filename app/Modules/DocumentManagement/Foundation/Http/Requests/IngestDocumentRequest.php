<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use Illuminate\Foundation\Http\FormRequest;
use RuntimeException;

class IngestDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('documents.upload') ?? false;
    }

    public function rules(): array
    {
        return [
            'owner_domain' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/'],
            'owner_aggregate_type' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/'],
            'owner_aggregate_id' => ['required', 'string', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
            'idempotency_key' => ['required', 'string', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
            'file' => ['required', 'file', 'max:'.intdiv((int) config('document-management.upload.max_bytes'), 1024)],
        ];
    }

    public function toDto(): IngestDocumentV1
    {
        $file = $this->file('file');
        $stream = $file === null ? false : fopen($file->getRealPath(), 'rb');
        if ($file === null || $stream === false) {
            throw new RuntimeException('Unable to open uploaded file stream.');
        }

        return new IngestDocumentV1(
            new DocumentOwnerContextV1(
                $this->string('owner_domain')->toString(),
                $this->string('owner_aggregate_type')->toString(),
                $this->string('owner_aggregate_id')->toString(),
            ),
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
