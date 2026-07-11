<?php

namespace App\Support\Modules;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class ModuleContractValidator
{
    /** @return array<int, array{module: string, path: string, code: string, message: string}> */
    public function validate(?string $only = null): array
    {
        $modules = $this->definitions();
        $errors = [];

        foreach ($modules as $module) {
            if ($only !== null && strcasecmp($only, $module['key']) !== 0) {
                continue;
            }

            $errors = [...$errors, ...$this->validateDefinition($module)];
        }

        foreach (collect($modules)->groupBy('key')->filter(fn ($group) => $group->count() > 1)->keys() as $duplicate) {
            $errors[] = $this->error($duplicate, '', 'duplicate_key', "Duplicate module key: {$duplicate}.");
        }

        foreach (collect($modules)->groupBy(fn (array $module) => strtolower($module['project'].'.'.$module['slug']))->filter(fn ($group) => $group->count() > 1)->keys() as $duplicate) {
            $errors[] = $this->error($duplicate, '', 'duplicate_slug', "Duplicate project slug: {$duplicate}.");
        }

        $known = collect($modules)->pluck('key')->all();
        foreach ($modules as $module) {
            foreach ($module['dependencies'] as $dependency) {
                $normalized = $this->normalizeDependency($dependency, $module['project']);
                if (! in_array($normalized, $known, true)) {
                    $errors[] = $this->error($module['key'], $module['path'], 'unknown_dependency', "Unknown dependency: {$dependency} ({$normalized}).");
                }
            }
        }

        return $errors;
    }

    /** @return array<int, array<string, mixed>> */
    private function definitions(): array
    {
        $root = (string) config('modules.backend_root', app_path('Modules'));
        if (! File::isDirectory($root)) {
            return [];
        }

        return collect(File::allFiles($root))
            ->filter(fn ($file) => $file->getFilename() === 'module.php')
            ->map(function ($file) {
                $path = $file->getPath();
                try {
                    $manifest = require $file->getPathname();
                } catch (Throwable $exception) {
                    $manifest = ['__load_error' => $exception->getMessage()];
                }
                $manifest = is_array($manifest) ? $manifest : [];
                $project = (string) ($manifest['project'] ?? basename(dirname($path)));
                $name = (string) ($manifest['name'] ?? basename($path));

                return [
                    'key' => $project.'.'.$name,
                    'project' => $project,
                    'name' => $name,
                    'slug' => (string) ($manifest['slug'] ?? ''),
                    'path' => $path,
                    'manifest' => $manifest,
                    'dependencies' => is_array($manifest['dependencies'] ?? null) ? $manifest['dependencies'] : [],
                ];
            })
            ->values()
            ->all();
    }

    /** @return array<int, array{module: string, path: string, code: string, message: string}> */
    private function validateDefinition(array $module): array
    {
        $errors = [];
        $manifest = $module['manifest'];
        $required = ['name', 'project', 'title', 'slug', 'description', 'version', 'enabled', 'providers', 'dependencies', 'exports', 'events', 'listeners', 'integrations'];

        foreach ($required as $field) {
            if (! array_key_exists($field, $manifest)) {
                $errors[] = $this->error($module['key'], $module['path'], 'missing_field', "Missing manifest field: {$field}.");
            }
        }

        if ($module['slug'] === '' || Str::kebab($module['slug']) !== $module['slug']) {
            $errors[] = $this->error($module['key'], $module['path'], 'invalid_slug', 'Slug must be non-empty kebab-case.');
        }

        foreach (['routes', 'permissions', 'navigation'] as $export) {
            $enabled = $manifest['exports'][$export] ?? null;
            if (! is_bool($enabled)) {
                $errors[] = $this->error($module['key'], $module['path'], 'invalid_export', "Export {$export} must be boolean.");
            } elseif ($enabled && ! File::exists($module['path'].DIRECTORY_SEPARATOR.$export.'.php')) {
                $errors[] = $this->error($module['key'], $module['path'], 'missing_export', "Export {$export} requires {$export}.php.");
            }
        }

        return $errors;
    }

    private function normalizeDependency(mixed $dependency, string $project): string
    {
        $value = trim((string) $dependency);
        $parts = array_values(array_filter(explode('.', str_replace(['/', '\\'], '.', $value))));

        return count($parts) === 1 ? $project.'.'.$parts[0] : $parts[0].'.'.$parts[count($parts) - 1];
    }

    /** @return array{module: string, path: string, code: string, message: string} */
    private function error(string $module, string $path, string $code, string $message): array
    {
        return compact('module', 'path', 'code', 'message');
    }
}
