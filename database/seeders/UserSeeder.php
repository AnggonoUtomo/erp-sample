<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Modules\ModulePermissionRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModulePermissionRegistry::permissions() as $permission) {
            Permission::findOrCreate($permission);
        }

        $superSystem = Role::findOrCreate(User::SUPER_SYSTEM_ROLE);
        $admin = Role::findOrCreate('admin');
        $staff = Role::findOrCreate('staff');

        $superSystem->syncPermissions(ModulePermissionRegistry::permissions());
        $admin->syncPermissions(ModulePermissionRegistry::defaultRolePermissions('admin'));
        $staff->syncPermissions(ModulePermissionRegistry::defaultRolePermissions('staff'));

        $superUser = User::updateOrCreate(
            ['email' => 'anggono@mail.com'],
            [
                'name' => 'Anggono',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $superUser->syncRoles([$superSystem]);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $adminUser->syncRoles([$admin]);

        $staffUser = User::updateOrCreate(
            ['email' => 'staff@mail.com'],
            [
                'name' => 'Staff Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $staffUser->syncRoles([$staff]);
    }
}
