<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Requests;

use App\Modules\DocumentManagement\Foundation\Delivery\DTO\IssueDocumentDeliveryV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use Illuminate\Foundation\Http\FormRequest;

class IssueDocumentDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'owner_domain' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/'],
            'owner_aggregate_type' => ['required', 'string', 'max:128', 'regex:/^[A-Za-z][A-Za-z0-9_-]*$/'],
            'owner_aggregate_id' => ['required', 'string', 'max:255', 'not_regex:/[\x00-\x1F\x7F]/'],
        ];
    }

    public function toDto(string $reference): IssueDocumentDeliveryV1
    {
        return new IssueDocumentDeliveryV1(
            new DocumentReferenceV1($reference),
            'user:'.$this->user()->getAuthIdentifier(),
            'DOWNLOAD',
            new DocumentOwnerContextV1(
                $this->string('owner_domain')->toString(),
                $this->string('owner_aggregate_type')->toString(),
                $this->string('owner_aggregate_id')->toString(),
            ),
        );
    }
}
