<?php

namespace App\Modules\Console\BackupRestores\Services;

use Illuminate\Validation\ValidationException;
use ZipArchive;

class FullBackupArchiveValidator
{
    private const MAX_ENTRIES = 10000;

    private const MAX_UNCOMPRESSED_BYTES = 536870912;

    public function validate(ZipArchive $zip): void
    {
        if ($zip->numFiles > self::MAX_ENTRIES) {
            $zip->close();
            throw ValidationException::withMessages(['backup' => 'ZIP memiliki terlalu banyak entry.']);
        }

        $totalSize = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = is_array($stat) ? ($stat['name'] ?? '') : '';
            $size = is_array($stat) ? (int) ($stat['size'] ?? 0) : 0;

            if (! is_string($name) || $name === '' || str_contains($name, "\0") || str_contains($name, '\\')) {
                $zip->close();
                throw ValidationException::withMessages(['backup' => 'ZIP memiliki nama entry yang tidak aman.']);
            }

            $segments = explode('/', $name);
            if (str_starts_with($name, '/') || preg_match('/^[A-Za-z]:/', $name) || in_array('..', $segments, true)) {
                $zip->close();
                throw ValidationException::withMessages(['backup' => "ZIP path traversal terdeteksi: {$name}."]);
            }

            $totalSize += $size;
            if ($totalSize > self::MAX_UNCOMPRESSED_BYTES) {
                $zip->close();
                throw ValidationException::withMessages(['backup' => 'Ukuran hasil ekstraksi ZIP melewati batas aman.']);
            }
        }
    }
}
