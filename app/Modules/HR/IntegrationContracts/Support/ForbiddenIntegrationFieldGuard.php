<?php

namespace App\Modules\HR\IntegrationContracts\Support;

use Illuminate\Support\Str;
use InvalidArgumentException;

class ForbiddenIntegrationFieldGuard
{
    /** @var array<int, string> */
    private const FORBIDDEN_KEYS = [
        'password',
        'password_hash',
        'remember_token',
        'reset_token',
        'reset_password_token',
        'token',
        'api_key',
        'secret',
        'nik',
        'ktp',
        'npwp',
        'national_id',
        'date_of_birth',
        'birth_date',
        'personal_phone',
        'phone_personal',
        'address',
        'home_address',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'bank_account',
        'bank_account_number',
        'salary',
        'compensation',
        'document_number',
        'document_reference',
        'dms_reference',
        'storage_path',
        'download_url',
        'url',
        'notes',
        'notes_internal',
        'internal_notes',
        'confidential_notes',
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    public function forbiddenFieldsIn(array $payload): array
    {
        return $this->scan($payload);
    }

    /** @param array<string, mixed> $payload */
    public function assertSafePayload(array $payload): void
    {
        $forbidden = $this->forbiddenFieldsIn($payload);

        if ($forbidden !== []) {
            throw new InvalidArgumentException(
                'Integration payload contains forbidden field(s): '.implode(', ', $forbidden).'.'
            );
        }
    }

    /**
     * @param  array<mixed>  $payload
     * @return array<int, string>
     */
    private function scan(array $payload): array
    {
        $found = [];

        foreach ($payload as $key => $value) {
            if (is_string($key) && $this->isForbiddenKey($key)) {
                $found[] = $key;
            }

            if (is_array($value)) {
                $found = [...$found, ...$this->scan($value)];
            }
        }

        return array_values(array_unique($found));
    }

    private function isForbiddenKey(string $key): bool
    {
        return in_array(Str::snake($key), self::FORBIDDEN_KEYS, true);
    }
}
