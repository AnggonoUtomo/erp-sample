<?php

namespace App\Modules\Console\BackupRestores\Services;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SqlDumpExecutor
{
    private const SQL_HEADER = '-- Laravel 12 Starterkit full database backup';

    public function run(string $sql): void
    {
        if (! str_starts_with(ltrim($sql), self::SQL_HEADER)) {
            throw ValidationException::withMessages([
                'backup' => 'SQL restore ditolak karena bukan dump yang dihasilkan aplikasi ini.',
            ]);
        }

        $connection = DB::connection();

        if (! in_array($connection->getDriverName(), ['mysql', 'sqlite'], true)) {
            throw ValidationException::withMessages([
                'backup' => 'Restore full database saat ini hanya mendukung koneksi MySQL dan SQLite.',
            ]);
        }

        $this->executeStatements($connection, $sql);
    }

    private function executeStatements(ConnectionInterface $connection, string $sql): void
    {
        $mysql = $connection->getDriverName() === 'mysql';

        if ($mysql) {
            $connection->unprepared('SET FOREIGN_KEY_CHECKS=0;');
        }

        try {
            foreach ($this->statements($sql) as $statement) {
                $trimmed = trim($statement);

                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }

                $connection->unprepared($trimmed);
            }
        } finally {
            if ($mysql) {
                $connection->unprepared('SET FOREIGN_KEY_CHECKS=1;');
            }
        }
    }

    /** @return array<int, string> */
    private function statements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $inString = false;
        $stringQuote = '';
        $escaped = false;

        for ($i = 0, $length = strlen($sql); $i < $length; $i++) {
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
}
