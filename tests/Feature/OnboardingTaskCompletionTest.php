<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingTaskCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.task-update'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_assigns_and_unassigns_active_internal_user(): void
    {
        $actor = $this->actor();
        $assignee = User::factory()->create();
        [$onboarding, $task] = $this->aggregate('DRAFT');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$onboarding, $task]), ['assignee_user_id' => $assignee->id])
            ->assertRedirect();
        $this->assertDatabaseHas('hr_onboarding_tasks', ['id' => $task->id, 'assignee_user_id' => $assignee->id]);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$onboarding, $task]), ['assignee_user_id' => null])
            ->assertRedirect();
        $this->assertDatabaseHas('hr_onboarding_tasks', ['id' => $task->id, 'assignee_user_id' => null]);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_deleted_assignee_and_task_from_other_onboarding_are_rejected(): void
    {
        $actor = $this->actor();
        $deletedAssignee = User::factory()->create();
        $deletedAssignee->delete();
        [$onboarding, $task] = $this->aggregate('DRAFT');
        [$otherOnboarding, $otherTask] = $this->aggregate('DRAFT', 'EMP-OTHER');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$onboarding, $task]), ['assignee_user_id' => $deletedAssignee->id])
            ->assertSessionHasErrors('assignee_user_id');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$otherOnboarding, $task]), ['assignee_user_id' => $actor->id])
            ->assertNotFound();

        $this->assertNull($otherTask->fresh()->assignee_user_id);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_task_moves_pending_to_in_progress_then_completed_with_evidence_and_progress(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 15)->setTime(9, 30));
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate('IN_PROGRESS');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.start', [$onboarding, $task]))
            ->assertRedirect();
        $this->assertSame('IN_PROGRESS', $task->fresh()->status->value);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.complete', [$onboarding, $task]), ['completion_note' => 'Akun dan orientasi dikonfirmasi.'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('COMPLETED', $task->status->value);
        $this->assertSame($actor->id, $task->completed_by_user_id);
        $this->assertSame('2026-07-15 09:30:00', $task->completed_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Akun dan orientasi dikonfirmasi.', $task->completion_note);
        $this->assertDatabaseCount('audit_logs', 2);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$onboarding, $task]), ['assignee_user_id' => $actor->id])
            ->assertSessionHasErrors('status');
        $this->assertNull($task->fresh()->assignee_user_id);
        $this->assertDatabaseCount('audit_logs', 2);

        $this->actingAs($actor)
            ->get(route('hr.onboardings.show', [$onboarding, 'business_date' => '2026-07-15']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('onboarding.progress.completed', 1)
                ->where('onboarding.progress.percentage', 100)
                ->where('onboarding.tasks.0.completed_by.id', $actor->id)
                ->where('onboarding.tasks.0.completion_note', 'Akun dan orientasi dikonfirmasi.')
                ->where('assigneeOptions', fn ($options) => collect($options)->contains('id', $actor->id)));
    }

    public function test_invalid_or_repeated_task_transition_does_not_add_audit(): void
    {
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate('IN_PROGRESS');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.complete', [$onboarding, $task]), ['completion_note' => 'Tidak valid'])
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)->patch(route('hr.onboardings.tasks.start', [$onboarding, $task]))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.onboardings.tasks.start', [$onboarding, $task]))->assertRedirect();

        $this->assertSame('IN_PROGRESS', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_terminal_onboarding_and_unauthorized_user_cannot_mutate_task(): void
    {
        $actor = $this->actor();
        $unauthorized = User::factory()->create();
        [$terminal, $terminalTask] = $this->aggregate('COMPLETED');
        [$active, $activeTask] = $this->aggregate('IN_PROGRESS', 'EMP-DENIED');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.assignment', [$terminal, $terminalTask]), ['assignee_user_id' => $actor->id])
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.start', [$terminal, $terminalTask]))
            ->assertSessionHasErrors('status');
        $this->actingAs($unauthorized)
            ->patch(route('hr.onboardings.tasks.start', [$active, $activeTask]))
            ->assertForbidden();

        $this->assertSame('PENDING', $terminalTask->fresh()->status->value);
        $this->assertSame('PENDING', $activeTask->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['onboardings.view', 'onboardings.task-update']);

        return $actor;
    }

    /** @return array{Onboarding, OnboardingTask} */
    private function aggregate(string $status, string $employeeNumber = 'EMP-TASK'): array
    {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $owner = User::factory()->create();
        $template = OnboardingTemplate::query()->create([
            'code' => "TASK-{$employee->id}",
            'name' => 'Task Template',
            'active' => true,
        ]);
        $onboarding = Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-15',
            'status' => $status,
            'active_identity_key' => "employee:{$employee->id}:start:2026-07-15",
            'request_fingerprint' => hash('sha256', "task-{$employee->id}"),
        ]);
        $task = $onboarding->tasks()->create([
            'title' => 'Siapkan akses',
            'category' => 'IT',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-15',
            'sort_order' => 0,
            'status' => 'PENDING',
        ]);

        return [$onboarding, $task];
    }
}
