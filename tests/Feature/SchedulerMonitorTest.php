<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SchedulerMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['scheduler-monitor.view', 'scheduler-monitor.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'scheduler-monitor.view',
            'scheduler-monitor.manage',
        ]);
    }

    public function test_authorized_users_can_view_scheduler_monitor_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('scheduler-monitor.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_run_due_scheduler_tasks(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('scheduler-monitor.run'))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_users_without_permission_cannot_run_scheduler_tasks(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('scheduler-monitor.run'))
            ->assertForbidden();
    }
}
