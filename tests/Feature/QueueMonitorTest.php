<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
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

        config(['queue.default' => 'database']);
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

    public function test_unauthorized_users_cannot_manage_failed_jobs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('queue-monitor.failed.retry', 'missing'))->assertForbidden();
        $this->actingAs($user)->delete(route('queue-monitor.failed.destroy', 'missing'))->assertForbidden();
        $this->actingAs($user)->delete(route('queue-monitor.failed.flush'))->assertForbidden();
    }

    public function test_failed_job_can_be_retried_to_the_pending_queue(): void
    {
        Queue::connection('database')->push(new AlwaysFailingQueueJob);

        $this->artisan('queue:work', [
            'connection' => 'database',
            '--once' => true,
            '--tries' => 1,
        ])->assertSuccessful();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 1);
        $uuid = (string) DB::table('failed_jobs')->value('uuid');

        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('queue-monitor.failed.retry', $uuid))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseCount('jobs', 1);
    }

    public function test_failed_job_exception_summary_redacts_sensitive_values(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        DB::table('failed_jobs')->insert([
            'uuid' => (string) str()->uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'SensitiveFailureJob']),
            'exception' => 'RuntimeException: token=super-secret api_key=maps-secret password=hunter2',
            'failed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('queue-monitor.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('failedJobs.data.0.exception', 'RuntimeException: token=[redacted] api_key=[redacted] password=[redacted]')
            );
    }
}

class AlwaysFailingQueueJob implements ShouldQueue
{
    use Queueable;

    public function handle(): never
    {
        throw new RuntimeException('Deterministic queue failure fixture.');
    }
}
