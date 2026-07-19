<?php

namespace App\Modules\Console\GlobalSearches\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return ['global-search.search'];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['global-search.search'],
            'staff' => ['global-search.search'],
        ];
    }
}
