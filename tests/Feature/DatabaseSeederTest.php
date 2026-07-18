<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_console_demo_users_and_roles(): void
    {
        $this->seed();

        $superSystem = User::where('email', 'anggono@mail.com')->firstOrFail();
        $admin = User::where('email', 'admin@mail.com')->firstOrFail();
        $staff = User::where('email', 'staff@mail.com')->firstOrFail();

        $this->assertTrue(Role::findByName('super-system')->hasPermissionTo('departements.manage'));
        $this->assertTrue(Role::findByName('admin')->hasPermissionTo('departements.manage'));
        $this->assertTrue(Role::findByName('staff')->hasPermissionTo('departements.view'));

        $this->assertTrue($superSystem->hasRole('super-system'));
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($staff->hasRole('staff'));
    }

    public function test_database_seeder_creates_hr_demo_users_and_roles(): void
    {
        $this->seed();

        $manager = User::where('email', 'hr.manager@mail.com')->firstOrFail();
        $officer = User::where('email', 'hr.officer@mail.com')->firstOrFail();
        $viewer = User::where('email', 'hr.viewer@mail.com')->firstOrFail();

        $this->assertTrue(Role::findByName('hr-manager')->hasPermissionTo('departements.manage'));
        $this->assertTrue(Role::findByName('hr-officer')->hasPermissionTo('departements.update'));
        $this->assertFalse(Role::findByName('hr-officer')->hasPermissionTo('departements.delete'));
        $this->assertTrue(Role::findByName('hr-viewer')->hasPermissionTo('departements.view'));
        $this->assertFalse(Role::findByName('hr-viewer')->hasPermissionTo('departements.create'));

        $this->assertTrue($manager->hasRole('hr-manager'));
        $this->assertTrue($officer->hasRole('hr-officer'));
        $this->assertTrue($viewer->hasRole('hr-viewer'));
    }
}
