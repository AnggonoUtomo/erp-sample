<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QueueMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['queue-monitor.view', 'queue-monitor.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions(['queue-monitor.view', 'queue-monitor.manage']);
    }

    public function test_authorized_users_can_view_queue_monitor(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'ExampleJob']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);

        $this->actingAs($user)
            ->get(route('queue-monitor.index'))
            ->assertOk();
    }

    public function test_unauthorized_users_cannot_view_queue_monitor(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('queue-monitor.index'))
            ->assertForbidden();
    }
}
