<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Modules\ModulePermissionRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HRUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModulePermissionRegistry::permissions() as $permission) {
            Permission::findOrCreate($permission);
        }

        $hrManager = Role::findOrCreate('hr-manager');
        $hrOfficer = Role::findOrCreate('hr-officer');
        $hrViewer = Role::findOrCreate('hr-viewer');

        $hrManager->syncPermissions(ModulePermissionRegistry::defaultRolePermissions('hr-manager'));
        $hrOfficer->syncPermissions(ModulePermissionRegistry::defaultRolePermissions('hr-officer'));
        $hrViewer->syncPermissions(ModulePermissionRegistry::defaultRolePermissions('hr-viewer'));

        $hrManagerUser = User::updateOrCreate(
            ['email' => 'hr.manager@mail.com'],
            [
                'name' => 'HR Manager Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $hrManagerUser->syncRoles([$hrManager]);

        $hrOfficerUser = User::updateOrCreate(
            ['email' => 'hr.officer@mail.com'],
            [
                'name' => 'HR Officer Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $hrOfficerUser->syncRoles([$hrOfficer]);

        $hrViewerUser = User::updateOrCreate(
            ['email' => 'hr.viewer@mail.com'],
            [
                'name' => 'HR Viewer Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $hrViewerUser->syncRoles([$hrViewer]);
    }
}
