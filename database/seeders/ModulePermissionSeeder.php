<?php

namespace Database\Seeders;

use App\Support\Modules\ModulePermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModulePermissionRegistry::permissions() as $permission) {
            Permission::findOrCreate($permission);
        }
    }
}
