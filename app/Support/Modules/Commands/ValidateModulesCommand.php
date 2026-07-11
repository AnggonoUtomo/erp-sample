<?php

namespace App\Support\Modules\Commands;

use App\Support\Modules\ModuleContractValidator;
use Illuminate\Console\Command;

class ValidateModulesCommand extends Command
{
    protected $signature = 'module:validate {module? : Optional Project.Module key} {--json : Emit machine-readable JSON}';

    protected $description = 'Validate module manifests, exports, slugs, and dependencies without changing files.';

    public function handle(ModuleContractValidator $validator): int
    {
        $errors = $validator->validate($this->argument('module'));

        if ($this->option('json')) {
            $this->output->writeln((string) json_encode([
                'valid' => $errors === [],
                'errors' => $errors,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } elseif ($errors === []) {
            $this->components->info('All module contracts are valid.');
        } else {
            $this->components->error(count($errors).' module contract violation(s) found.');
            $this->table(['Module', 'Code', 'Message'], collect($errors)->map(fn (array $error) => [
                $error['module'], $error['code'], $error['message'],
            ])->all());
        }

        return $errors === [] ? self::SUCCESS : self::FAILURE;
    }
}
