<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingTaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['offboardings.view', 'offboardings.task-update', 'offboardings.task-skip-required'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_optional_task_can_be_skipped_with_reason_and_evidence(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 16)->setTime(11, 30));
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate(required: false);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Tidak berlaku untuk proses ini.',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('SKIPPED', $task->status->value);
        $this->assertSame($actor->id, $task->skipped_by_user_id);
        $this->assertSame('2026-07-16 11:30:00', $task->skipped_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Tidak berlaku untuk proses ini.', $task->skip_reason);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'OffboardingTask.skipped',
            'auditable_id' => $task->id,
        ]);

        $this->actingAs($actor)
            ->get(route('hr.offboardings.show', [$offboarding, 'business_date' => '2026-07-16']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('offboarding.progress.percentage', 100)
                ->where('offboarding.tasks.0.skipped_by.id', $actor->id)
                ->where('offboarding.tasks.0.skip_reason', 'Tidak berlaku untuk proses ini.'));
    }

    public function test_required_skip_requires_special_permission_and_non_empty_reason(): void
    {
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate(required: true);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Pengecualian disetujui.',
            ])
            ->assertForbidden();
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), ['skip_reason' => ''])
            ->assertSessionHasErrors('skip_reason');

        $actor->givePermissionTo('offboardings.task-skip-required');
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Pengecualian disetujui HR Manager.',
            ])
            ->assertRedirect();

        $this->assertSame('SKIPPED', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_reopen_clears_terminal_evidence_and_ready_case_returns_to_in_progress_atomically(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 17)->setTime(8, 45));
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate(
            required: true,
            taskStatus: 'COMPLETED',
            offboardingStatus: 'READY_FOR_EXIT',
        );
        $task->update([
            'completed_by_user_id' => $actor->id,
            'completed_at' => '2026-07-16 12:00:00',
            'completion_note' => 'Selesai sebelumnya.',
        ]);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.reopen', [$offboarding, $task]), ['reopen_reason' => ''])
            ->assertSessionHasErrors('reopen_reason');
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.reopen', [$offboarding, $task]), [
                'reopen_reason' => 'Evidence perlu diperbaiki.',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('PENDING', $task->status->value);
        $this->assertNull($task->completed_by_user_id);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->completion_note);
        $this->assertSame($actor->id, $task->reopened_by_user_id);
        $this->assertSame('2026-07-17 08:45:00', $task->reopened_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Evidence perlu diperbaiki.', $task->reopen_reason);
        $this->assertSame('IN_PROGRESS', $offboarding->fresh()->status->value);
        $this->assertDatabaseHas('audit_logs', ['event' => 'OffboardingTask.reopened']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Offboarding.readiness_revoked']);
    }

    public function test_invalid_repeated_archived_terminal_and_unauthorized_mutations_fail_closed(): void
    {
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate(required: false);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.reopen', [$offboarding, $task]), [
                'reopen_reason' => 'Belum terminal.',
            ])
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Tidak diperlukan.',
            ])
            ->assertRedirect();
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Retry.',
            ])
            ->assertSessionHasErrors('status');

        [$archived, $archivedTask] = $this->aggregate(false, employeeNumber: 'EMP-OFF-SKIP-ARCHIVED');
        $archived->delete();
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$archived, $archivedTask]), [
                'skip_reason' => 'Tidak boleh.',
            ])
            ->assertSessionHasErrors('status');

        [$terminal, $terminalTask] = $this->aggregate(
            false,
            offboardingStatus: 'CANCELLED',
            employeeNumber: 'EMP-OFF-SKIP-TERMINAL',
        );
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$terminal, $terminalTask]), [
                'skip_reason' => 'Tidak boleh.',
            ])
            ->assertSessionHasErrors('status');

        $unauthorized = User::factory()->create();
        [$active, $activeTask] = $this->aggregate(false, employeeNumber: 'EMP-OFF-SKIP-DENIED');
        $this->actingAs($unauthorized)
            ->patch(route('hr.offboardings.tasks.skip', [$active, $activeTask]), [
                'skip_reason' => 'Tidak berizin.',
            ])
            ->assertForbidden();

        $this->assertSame('SKIPPED', $task->fresh()->status->value);
        $this->assertSame('PENDING', $archivedTask->fresh()->status->value);
        $this->assertSame('PENDING', $terminalTask->fresh()->status->value);
        $this->assertSame('PENDING', $activeTask->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_audit_failure_rolls_back_skip_evidence(): void
    {
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate(required: false);
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.skip', [$offboarding, $task]), [
                'skip_reason' => 'Harus rollback.',
            ])
            ->assertServerError();

        $task->refresh();
        $this->assertSame('PENDING', $task->status->value);
        $this->assertNull($task->skipped_by_user_id);
        $this->assertNull($task->skipped_at);
        $this->assertNull($task->skip_reason);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['offboardings.view', 'offboardings.task-update']);

        return $actor;
    }

    /** @return array{Offboarding, OffboardingTask} */
    private function aggregate(
        bool $required,
        string $taskStatus = 'PENDING',
        string $offboardingStatus = 'IN_PROGRESS',
        string $employeeNumber = 'EMP-OFF-TASK-LIFECYCLE',
    ): array {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => "LIFE-{$employee->id}",
            'name' => 'Lifecycle',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => "ENDED-LIFE-{$employee->id}",
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $offboarding = Offboarding::query()->create([
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Lifecycle test.',
            'status' => $offboardingStatus,
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "lifecycle-{$employee->id}"),
        ]);
        $task = $offboarding->tasks()->create([
            'title' => 'Lifecycle task',
            'category' => 'HR',
            'required' => $required,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => $taskStatus,
        ]);

        return [$offboarding, $task];
    }
}
