<?php

namespace App\Support\Modules;

class ModulePermissionRegistry
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return collect()
            ->merge(self::permissionsFromFiles())
            ->merge(self::permissionsFromProviders())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function defaultRolePermissions(string $role): array
    {
        return collect()
            ->merge(self::defaultRolePermissionsFromFiles($role))
            ->merge(self::defaultRolePermissionsFromProviders($role))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function permissionsFromFiles(): array
    {
        return ModuleRegistry::permissionFiles()
            ->flatMap(fn (string $path) => (require $path)['permissions'] ?? [])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function permissionsFromProviders(): array
    {
        return ModuleRegistry::permissionProviders()
            ->flatMap(fn (string $provider) => $provider::permissions())
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function defaultRolePermissionsFromFiles(string $role): array
    {
        return ModuleRegistry::permissionFiles()
            ->flatMap(fn (string $path) => (require $path)['roles'][$role] ?? [])
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function defaultRolePermissionsFromProviders(string $role): array
    {
        return ModuleRegistry::permissionProviders()
            ->flatMap(fn (string $provider) => $provider::defaultRolePermissions()[$role] ?? [])
            ->values()
            ->all();
    }
}
