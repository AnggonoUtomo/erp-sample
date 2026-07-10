<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get('/dashboard')->assertRedirect('/console/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $this->actingAs($user = User::factory()->create());

        $this->get('/dashboard')->assertOk();
    }

    public function test_authorized_users_can_visit_the_hr_dashboard()
    {
        Permission::findOrCreate('hr.view');
        Role::findOrCreate('hr-viewer')->syncPermissions(['hr.view']);

        $user = User::factory()->create();
        $user->assignRole('hr-viewer');

        $this->actingAs($user)
            ->get('/hr/dashboard')
            ->assertOk();
    }
}
