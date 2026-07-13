<?php

namespace App\Modules\HR\HRReferenceData\Support;

use Illuminate\Validation\Rule;

class ReferenceMetadataContract
{
    public const EMPLOYEE_DOCUMENT_TYPE = 'employee-document-type';

    public const DOCUMENT_NUMBER_UNIQUE_SCOPES = ['NONE', 'PER_EMPLOYEE', 'GLOBAL'];

    /** @return array<string, array<int, mixed>> */
    public static function rulesFor(string $category): array
    {
        if (str($category)->lower()->kebab()->toString() !== self::EMPLOYEE_DOCUMENT_TYPE) {
            return ['metadata' => ['nullable', 'array']];
        }

        return [
            'metadata' => ['required', 'array:requires_expiry,requires_number,number_unique_scope'],
            'metadata.requires_expiry' => ['required', 'boolean'],
            'metadata.requires_number' => ['required', 'boolean'],
            'metadata.number_unique_scope' => ['required', 'string', Rule::in(self::DOCUMENT_NUMBER_UNIQUE_SCOPES)],
        ];
    }
}
