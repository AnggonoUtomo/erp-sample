<?php

namespace App\Modules\Console\BackupRestores\Services;

use Illuminate\Validation\ValidationException;
use JsonException;
use RuntimeException;

class BackupSignatureService
{
    /** @param array<string, mixed> $manifest */
    public function sign(array $manifest): array
    {
        return [
            'algorithm' => 'hmac-sha256',
            'key_id' => $this->keyId(),
            'signature' => hash_hmac('sha256', $this->canonicalJson($manifest), $this->key()),
        ];
    }

    /** @param array<string, mixed> $manifest */
    public function verify(array $manifest): void
    {
        $authenticity = $manifest['authenticity'] ?? null;
        unset($manifest['authenticity']);

        if (! is_array($authenticity)
            || ($authenticity['algorithm'] ?? null) !== 'hmac-sha256'
            || ($authenticity['key_id'] ?? null) !== $this->keyId()
            || ! is_string($authenticity['signature'] ?? null)) {
            throw ValidationException::withMessages(['backup' => 'Signature full backup tidak valid atau key ID tidak dikenal.']);
        }

        $expected = hash_hmac('sha256', $this->canonicalJson($manifest), $this->key());
        if (! hash_equals($expected, $authenticity['signature'])) {
            throw ValidationException::withMessages(['backup' => 'Signature full backup tidak valid. Arsip bukan berasal dari environment tepercaya.']);
        }
    }

    private function key(): string
    {
        $key = config('backup.signature_key');
        if (! is_string($key) || strlen($key) < 32) {
            throw new RuntimeException('BACKUP_SIGNATURE_KEY wajib diisi minimal 32 karakter.');
        }

        return $key;
    }

    private function keyId(): string
    {
        $keyId = config('backup.signature_key_id');
        if (! is_string($keyId) || trim($keyId) === '') {
            throw new RuntimeException('BACKUP_SIGNATURE_KEY_ID wajib diisi.');
        }

        return $keyId;
    }

    /** @param array<string, mixed> $value */
    private function canonicalJson(array $value): string
    {
        try {
            return json_encode($this->normalize($value), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Manifest backup tidak dapat ditandatangani.', previous: $exception);
        }
    }

    private function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item) => $this->normalize($item), $value);
        }

        ksort($value, SORT_STRING);

        return array_map(fn (mixed $item) => $this->normalize($item), $value);
    }
}
