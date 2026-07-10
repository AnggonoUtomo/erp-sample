<?php

namespace App\Modules\Console\BackupRestores\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\NotificationTemplates\Models\NotificationTemplate;
use App\Modules\Console\SystemSettings\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use JsonException;
use PDO;
use RuntimeException;
use ZipArchive;

class BackupRestoreService
{
    private const VERSION = 1;

    public function __construct(
        private readonly AuditLogService $audit,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $storagePublicPath = storage_path('app/public');

        return [
            'system_settings' => SystemSetting::query()->count(),
            'encrypted_settings' => SystemSetting::query()->where('encrypted', true)->count(),
            'notification_templates' => NotificationTemplate::query()->count(),
            'database_connection' => config('database.default'),
            'database_name' => config('database.connections.'.config('database.default').'.database'),
            'storage_public_exists' => File::isDirectory($storagePublicPath),
            'storage_public_size' => $this->directorySize($storagePublicPath),
            'last_ready_at' => now()->format('d M Y H:i:s'),
            'included_sections' => [
                'system_settings',
                'notification_templates',
            ],
            'excluded_sections' => [
                'users',
                'roles_permissions',
                'audit_logs',
                'login_activities',
                'media_files',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function export(): array
    {
        $payload = [
            'schema' => 'laravel12-starterkit.settings-backup',
            'version' => self::VERSION,
            'exported_at' => now()->toISOString(),
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'environment' => config('app.env'),
            ],
            'sections' => [
                'system_settings' => SystemSetting::query()
                    ->orderBy('group')
                    ->orderBy('key')
                    ->get(['group', 'key', 'value', 'encrypted'])
                    ->map(fn (SystemSetting $setting) => [
                        'group' => $setting->group,
                        'key' => $setting->key,
                        'value' => $setting->value,
                        'encrypted' => $setting->encrypted,
                    ])
                    ->values()
                    ->all(),
                'notification_templates' => NotificationTemplate::query()
                    ->orderBy('key')
                    ->get(['key', 'name', 'channel', 'subject', 'body', 'variables', 'active'])
                    ->map(fn (NotificationTemplate $template) => [
                        'key' => $template->key,
                        'name' => $template->name,
                        'channel' => $template->channel,
                        'subject' => $template->subject,
                        'body' => $template->body,
                        'variables' => $template->variables,
                        'active' => $template->active,
                    ])
                    ->values()
                    ->all(),
            ],
        ];

        $this->audit->record(
            module: 'backup-restore',
            event: 'settings.exported',
            description: 'Exported settings backup',
            newValues: [
                'system_settings' => count($payload['sections']['system_settings']),
                'notification_templates' => count($payload['sections']['notification_templates']),
            ],
        );

        return $payload;
    }

    /**
     * @return array<string, int>
     */
    public function restore(UploadedFile $file, bool $restoreSystemSettings, bool $restoreNotificationTemplates): array
    {
        if (! $restoreSystemSettings && ! $restoreNotificationTemplates) {
            throw ValidationException::withMessages([
                'backup' => 'Pilih minimal satu section yang ingin direstore.',
            ]);
        }

        $payload = $this->settingsBackupPayload($file);

        if ((int) ($payload['version'] ?? 0) !== self::VERSION) {
            throw ValidationException::withMessages([
                'backup' => 'Versi backup tidak kompatibel dengan aplikasi ini.',
            ]);
        }

        $sections = $payload['sections'] ?? [];
        $summary = [
            'system_settings' => 0,
            'notification_templates' => 0,
        ];

        DB::transaction(function () use ($sections, $restoreSystemSettings, $restoreNotificationTemplates, &$summary) {
            if ($restoreSystemSettings) {
                foreach (($sections['system_settings'] ?? []) as $setting) {
                    if (! is_array($setting) || blank($setting['group'] ?? null) || blank($setting['key'] ?? null)) {
                        continue;
                    }

                    SystemSetting::query()->updateOrCreate(
                        [
                            'group' => $setting['group'],
                            'key' => $setting['key'],
                        ],
                        [
                            'value' => $setting['value'] ?? null,
                            'encrypted' => (bool) ($setting['encrypted'] ?? false),
                        ],
                    );

                    $summary['system_settings']++;
                }
            }

            if ($restoreNotificationTemplates) {
                foreach (($sections['notification_templates'] ?? []) as $template) {
                    if (! is_array($template) || blank($template['key'] ?? null)) {
                        continue;
                    }

                    NotificationTemplate::query()->updateOrCreate(
                        ['key' => $template['key']],
                        [
                            'name' => $template['name'] ?? str($template['key'])->headline()->toString(),
                            'channel' => $template['channel'] ?? 'mail',
                            'subject' => $template['subject'] ?? null,
                            'body' => $template['body'] ?? null,
                            'variables' => $template['variables'] ?? [],
                            'active' => (bool) ($template['active'] ?? true),
                        ],
                    );

                    $summary['notification_templates']++;
                }
            }
        });

        $this->audit->record(
            module: 'backup-restore',
            event: 'settings.restored',
            description: 'Restored settings backup',
            newValues: $summary,
        );

        return $summary;
    }

    /**
     * @return array<string, mixed>
     */
    private function settingsBackupPayload(UploadedFile $file): array
    {
        try {
            $payload = json_decode($file->get(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'backup' => 'Isi file bukan JSON valid. Pastikan file berasal dari tombol Download Backup JSON.',
            ]);
        }

        if (! is_array($payload)) {
            throw ValidationException::withMessages([
                'backup' => 'Root JSON harus berupa object backup setting.',
            ]);
        }

        $schema = $payload['schema'] ?? null;

        if ($schema === 'laravel12-starterkit.full-backup') {
            throw ValidationException::withMessages([
                'backup' => 'File JSON ini adalah manifest full backup. Untuk full restore, upload file .zip atau .sql pada panel Full Restore.',
            ]);
        }

        if ($schema !== 'laravel12-starterkit.settings-backup') {
            $schemaMessage = is_string($schema) && $schema !== ''
                ? "Schema yang terbaca: {$schema}."
                : 'Field schema tidak ditemukan.';

            throw ValidationException::withMessages([
                'backup' => "File JSON valid, tapi bukan Settings Backup aplikasi ini. {$schemaMessage} Gunakan file dari tombol Download Backup JSON.",
            ]);
        }

        if (! is_array($payload['sections'] ?? null)) {
            throw ValidationException::withMessages([
                'backup' => 'Struktur sections tidak ditemukan di file backup setting.',
            ]);
        }

        return $payload;
    }

    public function createFullBackupZip(): string
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
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'environment' => config('app.env'),
            ],
            'database' => [
                'connection' => config('database.default'),
                'name' => config('database.connections.'.config('database.default').'.database'),
            ],
            'includes' => [
                'database.sql',
                'storage_public',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->addFromString('database.sql', $this->databaseDumpSql());
        $this->addDirectoryToZip($zip, storage_path('app/public'), 'storage_public');
        $zip->close();

        $this->audit->record(
            module: 'backup-restore',
            event: 'full_backup.exported',
            description: 'Exported full database and storage backup',
            newValues: [
                'path' => basename($path),
                'database' => config('database.default'),
                'storage_public_size' => $this->directorySize(storage_path('app/public')),
            ],
        );

        return $path;
    }

    /**
     * @return array<string, int|bool>
     */
    public function restoreFullBackup(UploadedFile $file, bool $restoreDatabase, bool $restoreStoragePublic): array
    {
        if (! $restoreDatabase && ! $restoreStoragePublic) {
            throw ValidationException::withMessages([
                'backup' => 'Pilih minimal database atau storage public untuk full restore.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $summary = [
            'database_restored' => false,
            'storage_files_restored' => 0,
        ];

        if ($extension === 'zip') {
            $summary = $this->restoreFromFullBackupZip($file, $restoreDatabase, $restoreStoragePublic);
        } elseif ($restoreDatabase) {
            $this->runSqlDump($file->get());
            $summary['database_restored'] = true;
        } else {
            throw ValidationException::withMessages([
                'backup' => 'File SQL hanya bisa digunakan untuk restore database.',
            ]);
        }

        $this->audit->record(
            module: 'backup-restore',
            event: 'full_backup.restored',
            description: 'Restored full backup',
            newValues: $summary,
        );

        return $summary;
    }

    private function databaseDumpSql(): string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            return $this->sqliteDumpSql();
        }

        if ($driver !== 'mysql') {
            throw new RuntimeException('Full database backup saat ini mendukung koneksi MySQL dan SQLite.');
        }

        $pdo = $connection->getPdo();
        $database = $connection->getDatabaseName();
        $tables = collect($connection->select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']))
            ->map(fn (object $row) => array_values((array) $row)[0])
            ->values();

        $sql = [
            '-- Laravel 12 Starterkit full database backup',
            '-- Exported at: '.now()->toISOString(),
            '-- Database: '.$database,
            'SET FOREIGN_KEY_CHECKS=0;',
            'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";',
            '',
        ];

        foreach ($tables as $table) {
            $quotedTable = $this->quoteIdentifier($table);
            $create = (array) $connection->selectOne("SHOW CREATE TABLE {$quotedTable}");
            $createStatement = $create['Create Table'] ?? array_values($create)[1] ?? null;

            if (! $createStatement) {
                continue;
            }

            $sql[] = "DROP TABLE IF EXISTS {$quotedTable};";
            $sql[] = $createStatement.';';
            $sql[] = '';

            $connection->table($table)->orderByRaw('1')->chunk(500, function ($rows) use (&$sql, $pdo, $table, $quotedTable) {
                foreach ($rows as $row) {
                    $values = (array) $row;
                    $columns = collect(array_keys($values))->map(fn (string $column) => $this->quoteIdentifier($column))->implode(', ');
                    $serializedValues = collect($values)
                        ->map(fn (mixed $value) => $value === null ? 'NULL' : $pdo->quote((string) $value))
                        ->implode(', ');

                    $sql[] = "INSERT INTO {$quotedTable} ({$columns}) VALUES ({$serializedValues});";
                }

                if ($rows->isNotEmpty()) {
                    $sql[] = '';
                }
            });
        }

        $sql[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $sql[] = '';

        return implode(PHP_EOL, $sql);
    }

    private function sqliteDumpSql(): string
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $tables = collect($connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
            ->pluck('name')
            ->values();

        $sql = [
            '-- Laravel 12 Starterkit full database backup',
            '-- Exported at: '.now()->toISOString(),
            '-- Driver: sqlite',
            'PRAGMA foreign_keys=OFF;',
            'BEGIN TRANSACTION;',
            '',
        ];

        foreach ($tables as $table) {
            $quotedTable = $this->quoteSqliteIdentifier($table);
            $schema = $connection->selectOne('SELECT sql FROM sqlite_master WHERE type = ? AND name = ?', ['table', $table]);
            $createStatement = $schema?->sql;

            if (! $createStatement) {
                continue;
            }

            $sql[] = "DROP TABLE IF EXISTS {$quotedTable};";
            $sql[] = $createStatement.';';

            foreach ($connection->table($table)->get() as $row) {
                $values = (array) $row;
                $columns = collect(array_keys($values))->map(fn (string $column) => $this->quoteSqliteIdentifier($column))->implode(', ');
                $serializedValues = collect($values)
                    ->map(fn (mixed $value) => $value === null ? 'NULL' : $pdo->quote((string) $value))
                    ->implode(', ');

                $sql[] = "INSERT INTO {$quotedTable} ({$columns}) VALUES ({$serializedValues});";
            }

            $sql[] = '';
        }

        $sql[] = 'COMMIT;';
        $sql[] = 'PRAGMA foreign_keys=ON;';
        $sql[] = '';

        return implode(PHP_EOL, $sql);
    }

    private function runSqlDump(string $sql): void
    {
        $connection = DB::connection();

        if (! in_array($connection->getDriverName(), ['mysql', 'sqlite'], true)) {
            throw ValidationException::withMessages([
                'backup' => 'Restore full database saat ini hanya mendukung koneksi MySQL dan SQLite.',
            ]);
        }

        if ($connection->getDriverName() === 'mysql') {
            $connection->unprepared('SET FOREIGN_KEY_CHECKS=0;');
        }

        foreach ($this->splitSqlStatements($sql) as $statement) {
            $trimmed = trim($statement);

            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }

            $connection->unprepared($trimmed);
        }

        if ($connection->getDriverName() === 'mysql') {
            $connection->unprepared('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * @return array<int, string>
     */
    private function splitSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringQuote = '';
        $escaped = false;

        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $buffer .= $char;

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\' && $inString) {
                $escaped = true;
                continue;
            }

            if (($char === "'" || $char === '"') && (! $inString || $stringQuote === $char)) {
                $inString = ! $inString;
                $stringQuote = $inString ? $char : '';
                continue;
            }

            if ($char === ';' && ! $inString) {
                $statements[] = $buffer;
                $buffer = '';
            }
        }

        if (trim($buffer) !== '') {
            $statements[] = $buffer;
        }

        return $statements;
    }

    private function restoreFromFullBackupZip(UploadedFile $file, bool $restoreDatabase, bool $restoreStoragePublic): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw ValidationException::withMessages([
                'backup' => 'PHP ZipArchive extension belum aktif.',
            ]);
        }

        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            throw ValidationException::withMessages([
                'backup' => 'File ZIP tidak bisa dibuka.',
            ]);
        }

        $manifestContent = $zip->getFromName('manifest.json');
        $manifest = $manifestContent ? json_decode($manifestContent, true) : null;

        if (! is_array($manifest) || ($manifest['schema'] ?? null) !== 'laravel12-starterkit.full-backup') {
            $zip->close();

            throw ValidationException::withMessages([
                'backup' => 'File ZIP bukan full backup aplikasi ini.',
            ]);
        }

        $summary = [
            'database_restored' => false,
            'storage_files_restored' => 0,
        ];

        if ($restoreDatabase) {
            $sql = $zip->getFromName('database.sql');

            if (! $sql) {
                $zip->close();

                throw ValidationException::withMessages([
                    'backup' => 'database.sql tidak ditemukan di dalam ZIP.',
                ]);
            }

            $this->runSqlDump($sql);
            $summary['database_restored'] = true;
        }

        if ($restoreStoragePublic) {
            $summary['storage_files_restored'] = $this->extractStoragePublic($zip);
        }

        $zip->close();

        return $summary;
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $prefix): void
    {
        if (! File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $zip->addFile($file->getRealPath(), trim($prefix, '/').'/'.$relative);
        }
    }

    private function extractStoragePublic(ZipArchive $zip): int
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

            if (str_contains($relative, '..') || str_starts_with($relative, '/') || preg_match('/^[A-Za-z]:/', $relative)) {
                continue;
            }

            $destination = $target.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            File::ensureDirectoryExists(dirname($destination));
            file_put_contents($destination, $zip->getFromIndex($i));
            $restored++;
        }

        return $restored;
    }

    private function directorySize(string $directory): int
    {
        if (! File::isDirectory($directory)) {
            return 0;
        }

        return collect(File::allFiles($directory))->sum(fn ($file) => $file->getSize());
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    private function quoteSqliteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
