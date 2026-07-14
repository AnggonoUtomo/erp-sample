<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.complete', 'onboardings.cancel', 'onboardings.task-update'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_completion_requires_every_required_task_to_be_terminal_and_records_evidence(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.complete');
        $onboarding = $this->onboarding('IN_PROGRESS');
        $required = $this->task($onboarding, true, 'PENDING', 1);
        $this->task($onboarding, false, 'PENDING', 2);

        $this->actingAs($actor)->patch(route('hr.onboardings.complete', $onboarding))
            ->assertSessionHasErrors('status');

        $required->update(['status' => 'SKIPPED']);

        $this->actingAs($actor)->patch(route('hr.onboardings.complete', $onboarding))->assertRedirect();

        $this->assertDatabaseHas('hr_onboardings', [
            'id' => $onboarding->id,
            'status' => 'COMPLETED',
            'completed_by_user_id' => $actor->id,
        ]);
        $this->assertNotNull($onboarding->fresh()->completed_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Onboarding.completed', 'auditable_id' => $onboarding->id]);
    }

    public function test_cancel_requires_reason_for_draft_and_in_progress_and_records_evidence(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.cancel');

        foreach (['DRAFT', 'IN_PROGRESS'] as $status) {
            $onboarding = $this->onboarding($status);
            $this->actingAs($actor)->patch(route('hr.onboardings.cancel', $onboarding), [])->assertSessionHasErrors('reason');
            $this->actingAs($actor)->patch(route('hr.onboardings.cancel', $onboarding), ['reason' => "Dibatalkan dari {$status}"])->assertRedirect();

            $fresh = $onboarding->fresh();
            $this->assertSame('CANCELLED', $fresh->status->value);
            $this->assertSame($actor->id, $fresh->cancelled_by_user_id);
            $this->assertSame("Dibatalkan dari {$status}", $fresh->cancel_reason);
            $this->assertNotNull($fresh->cancelled_at);
        }

        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_terminal_onboarding_rejects_repeated_lifecycle_and_task_mutations(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['onboardings.complete', 'onboardings.cancel', 'onboardings.task-update']);
        $onboarding = $this->onboarding('IN_PROGRESS');
        $task = $this->task($onboarding, true, 'COMPLETED', 1);

        $this->actingAs($actor)->patch(route('hr.onboardings.complete', $onboarding))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.onboardings.complete', $onboarding))->assertSessionHasErrors('status');
        $this->actingAs($actor)->patch(route('hr.onboardings.cancel', $onboarding), ['reason' => 'Tidak boleh'])->assertSessionHasErrors('status');
        $this->actingAs($actor)->patch(route('hr.onboardings.tasks.reopen', [$onboarding, $task]), ['reopen_reason' => 'Tidak boleh'])->assertSessionHasErrors('status');

        $this->assertSame('COMPLETED', $onboarding->fresh()->status->value);
        $this->assertSame('COMPLETED', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_lifecycle_routes_are_permission_protected(): void
    {
        $actor = User::factory()->create();
        $onboarding = $this->onboarding('IN_PROGRESS');

        $this->actingAs($actor)->patch(route('hr.onboardings.complete', $onboarding))->assertForbidden();
        $this->actingAs($actor)->patch(route('hr.onboardings.cancel', $onboarding), ['reason' => 'Unauthorized'])->assertForbidden();

        $this->assertSame('IN_PROGRESS', $onboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function onboarding(string $status): Onboarding
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-LIFE-'.uniqid(),
            'first_name' => 'Lifecycle',
            'display_name' => 'Lifecycle User',
            'active' => true,
        ]);
        $template = OnboardingTemplate::query()->create(['code' => 'LIFE-'.uniqid(), 'name' => 'Lifecycle', 'active' => true]);

        return Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => User::factory()->create()->id,
            'start_date' => '2026-07-20',
            'status' => $status,
            'active_identity_key' => "employee:{$employee->id}:start:2026-07-20",
            'request_fingerprint' => hash('sha256', "lifecycle-{$employee->id}"),
        ]);
    }

    private function task(Onboarding $onboarding, bool $required, string $status, int $sortOrder): OnboardingTask
    {
        return $onboarding->tasks()->create([
            'title' => "Task {$sortOrder}",
            'category' => 'HR',
            'required' => $required,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => $sortOrder,
            'status' => $status,
        ]);
    }
}
