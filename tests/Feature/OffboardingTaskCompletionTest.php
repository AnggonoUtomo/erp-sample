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

class OffboardingTaskCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['offboardings.view', 'offboardings.task-update'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_assigns_and_unassigns_active_console_user(): void
    {
        $actor = $this->actor();
        $assignee = User::factory()->create();
        [$offboarding, $task] = $this->aggregate('DRAFT');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.assignment', [$offboarding, $task]), ['assignee_user_id' => $assignee->id])
            ->assertRedirect();
        $this->assertDatabaseHas('hr_offboarding_tasks', ['id' => $task->id, 'assignee_user_id' => $assignee->id]);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.assignment', [$offboarding, $task]), ['assignee_user_id' => null])
            ->assertRedirect();
        $this->assertDatabaseHas('hr_offboarding_tasks', ['id' => $task->id, 'assignee_user_id' => null]);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_deleted_assignee_and_cross_aggregate_task_are_rejected(): void
    {
        $actor = $this->actor();
        $deletedAssignee = User::factory()->create();
        $deletedAssignee->delete();
        [$offboarding, $task] = $this->aggregate('DRAFT');
        [$otherOffboarding, $otherTask] = $this->aggregate('DRAFT', 'EMP-OFF-TASK-OTHER');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.assignment', [$offboarding, $task]), ['assignee_user_id' => $deletedAssignee->id])
            ->assertSessionHasErrors('assignee_user_id');
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.assignment', [$otherOffboarding, $task]), ['assignee_user_id' => $actor->id])
            ->assertNotFound();

        $this->assertNull($otherTask->fresh()->assignee_user_id);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_task_starts_then_completes_with_actor_time_note_and_progress(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 16)->setTime(10, 15));
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate('IN_PROGRESS');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.start', [$offboarding, $task]))
            ->assertRedirect();
        $this->assertSame('IN_PROGRESS', $task->fresh()->status->value);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.complete', [$offboarding, $task]), [
                'completion_note' => 'Akses dan handover sudah dikonfirmasi.',
            ])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('COMPLETED', $task->status->value);
        $this->assertSame($actor->id, $task->completed_by_user_id);
        $this->assertSame('2026-07-16 10:15:00', $task->completed_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Akses dan handover sudah dikonfirmasi.', $task->completion_note);
        $this->assertDatabaseCount('audit_logs', 2);

        $this->actingAs($actor)
            ->get(route('hr.offboardings.show', [$offboarding, 'business_date' => '2026-07-16']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('offboarding.progress.completed', 1)
                ->where('offboarding.progress.percentage', 100)
                ->where('offboarding.tasks.0.completed_by.id', $actor->id)
                ->where('offboarding.tasks.0.completion_note', 'Akses dan handover sudah dikonfirmasi.')
                ->where('assigneeOptions', fn ($options) => collect($options)->contains('id', $actor->id)));
    }

    public function test_invalid_and_repeated_transitions_are_idempotent_without_extra_audit(): void
    {
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate('IN_PROGRESS');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.complete', [$offboarding, $task]), ['completion_note' => 'Belum dimulai'])
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)->patch(route('hr.offboardings.tasks.start', [$offboarding, $task]))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.offboardings.tasks.start', [$offboarding, $task]))->assertRedirect();

        $this->assertSame('IN_PROGRESS', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_terminal_archived_and_unauthorized_mutations_are_denied(): void
    {
        $actor = $this->actor();
        $unauthorized = User::factory()->create();
        [$terminal, $terminalTask] = $this->aggregate('COMPLETED');
        [$archived, $archivedTask] = $this->aggregate('IN_PROGRESS', 'EMP-OFF-TASK-ARCHIVED');
        $archived->delete();
        [$active, $activeTask] = $this->aggregate('IN_PROGRESS', 'EMP-OFF-TASK-DENIED');

        foreach ([[$terminal, $terminalTask], [$archived, $archivedTask]] as [$offboarding, $task]) {
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.tasks.start', [$offboarding, $task]))
                ->assertSessionHasErrors('status');
        }
        $this->actingAs($unauthorized)
            ->patch(route('hr.offboardings.tasks.start', [$active, $activeTask]))
            ->assertForbidden();

        $this->assertSame('PENDING', $terminalTask->fresh()->status->value);
        $this->assertSame('PENDING', $archivedTask->fresh()->status->value);
        $this->assertSame('PENDING', $activeTask->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_audit_failure_rolls_back_completion_evidence(): void
    {
        $actor = $this->actor();
        [$offboarding, $task] = $this->aggregate('IN_PROGRESS');
        $task->update(['status' => 'IN_PROGRESS']);
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.tasks.complete', [$offboarding, $task]), ['completion_note' => 'Harus rollback'])
            ->assertServerError();

        $task->refresh();
        $this->assertSame('IN_PROGRESS', $task->status->value);
        $this->assertNull($task->completed_by_user_id);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->completion_note);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['offboardings.view', 'offboardings.task-update']);

        return $actor;
    }

    /** @return array{Offboarding, OffboardingTask} */
    private function aggregate(string $status, string $employeeNumber = 'EMP-OFF-TASK'): array
    {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $owner = User::factory()->create();
        $template = OffboardingTemplate::query()->create([
            'code' => "TASK-{$employee->id}",
            'name' => 'Task Template',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => "ENDED-TASK-{$employee->id}",
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $offboarding = Offboarding::query()->create([
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Task completion test.',
            'status' => $status,
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "task-{$employee->id}"),
        ]);
        $task = $offboarding->tasks()->create([
            'title' => 'Selesaikan handover',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'PENDING',
        ]);

        return [$offboarding, $task];
    }
}
