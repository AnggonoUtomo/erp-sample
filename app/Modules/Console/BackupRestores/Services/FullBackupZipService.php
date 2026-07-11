<?php

namespace App\Modules\Console\BackupRestores\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use ZipArchive;

class FullBackupZipService
{
    private const VERSION = 2;

    public function __construct(
        private readonly FullBackupArchiveValidator $archiveValidator,
        private readonly SqlDumpExecutor $sqlExecutor,
    ) {}

    public function create(string $databaseSql): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('PHP ZipArchive extension belum aktif.');
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);
        $path = $backupDir.DIRECTORY_SEPARATOR.'full-backup-'.now()->format('Ymd-His').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak bisa membuat file backup ZIP.');
        }

        $zip->addFromString('manifest.json', json_encode([
            'schema' => 'laravel12-starterkit.full-backup',
            'version' => self::VERSION,
            'exported_at' => now()->toISOString(),
            'app' => ['name' => config('app.name'), 'url' => config('app.url'), 'environment' => config('app.env')],
            'database' => [
                'connection' => config('database.default'),
                'name' => config('database.connections.'.config('database.default').'.database'),
            ],
            'includes' => ['database.sql', 'storage_public'],
            'integrity' => ['database_sql_sha256' => hash('sha256', $databaseSql)],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->addFromString('database.sql', $databaseSql);
        $this->addDirectory($zip, storage_path('app/public'), 'storage_public');
        $zip->close();

        return $path;
    }

    /** @return array{database_restored: bool, storage_files_restored: int} */
    public function restore(UploadedFile $file, bool $restoreDatabase, bool $restoreStoragePublic): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages(['backup' => 'PHP ZipArchive extension belum aktif.']);
        }

        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            throw ValidationException::withMessages(['backup' => 'File ZIP tidak bisa dibuka.']);
        }

        $this->archiveValidator->validate($zip);
        $manifestContent = $zip->getFromName('manifest.json');
        $manifest = $manifestContent ? json_decode($manifestContent, true) : null;

        if (! is_array($manifest) || ($manifest['schema'] ?? null) !== 'laravel12-starterkit.full-backup') {
            $zip->close();
            throw ValidationException::withMessages(['backup' => 'File ZIP bukan full backup aplikasi ini.']);
        }

        if (($manifest['version'] ?? null) !== self::VERSION) {
            $zip->close();
            throw ValidationException::withMessages(['backup' => 'Versi full backup tidak didukung. Buat backup baru dengan versi aplikasi saat ini.']);
        }

        $summary = ['database_restored' => false, 'storage_files_restored' => 0];
        if ($restoreDatabase) {
            $sql = $zip->getFromName('database.sql');
            if (! $sql) {
                $zip->close();
                throw ValidationException::withMessages(['backup' => 'database.sql tidak ditemukan di dalam ZIP.']);
            }

            $expectedHash = $manifest['integrity']['database_sql_sha256'] ?? null;
            if (! is_string($expectedHash) || ! hash_equals($expectedHash, hash('sha256', $sql))) {
                $zip->close();
                throw ValidationException::withMessages(['backup' => 'Checksum database.sql tidak valid. File backup mungkin rusak atau telah diubah.']);
            }

            $this->sqlExecutor->run($sql);
            $summary['database_restored'] = true;
        }

        if ($restoreStoragePublic) {
            $summary['storage_files_restored'] = $this->extractStorage($zip);
        }

        $zip->close();

        return $summary;
    }

    private function addDirectory(ZipArchive $zip, string $directory, string $prefix): void
    {
        if (! File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), trim($prefix, '/').'/'.$relative);
        }
    }

    private function extractStorage(ZipArchive $zip): int
    {
        $target = storage_path('app/public');
        File::ensureDirectoryExists($target);
        $restored = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (! str_starts_with($name, 'storage_public/') || str_ends_with($name, '/')) {
                continue;
            }

            $relative = substr($name, strlen('storage_public/'));
            $destination = $target.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($destination));
            $contents = $zip->getFromIndex($i);
            if (! is_string($contents) || file_put_contents($destination, $contents, LOCK_EX) === false) {
                throw ValidationException::withMessages(['backup' => "Gagal mengekstrak file storage: {$name}."]);
            }
            $restored++;
        }

        return $restored;
    }
}
