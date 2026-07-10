<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['audit-logs.view', 'users.create', 'users.view'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions(['audit-logs.view', 'users.create', 'users.view']);
        Role::findOrCreate('staff');
    }

    public function test_authorized_users_can_view_audit_logs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        AuditLog::query()->create([
            'actor_id' => $user->id,
            'module' => 'testing',
            'event' => 'testing.created',
            'description' => 'Created testing data',
        ]);

        $this->actingAs($user)
            ->get(route('audit-logs.index'))
            ->assertOk();
    }

    public function test_user_creation_writes_audit_log(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Audited User',
                'email' => 'audited@example.com',
                'roles' => ['staff'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'module' => 'user-management',
            'event' => 'user.created',
            'description' => 'Created user audited@example.com',
        ]);
    }
}
