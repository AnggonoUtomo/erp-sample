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

class OnboardingTaskLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.task-update', 'onboardings.task-skip-required'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_optional_task_can_be_skipped_with_reason_and_evidence(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 16)->setTime(10, 15));
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate(required: false);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => 'Tidak diperlukan untuk lokasi ini.'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('SKIPPED', $task->status->value);
        $this->assertSame($actor->id, $task->skipped_by_user_id);
        $this->assertSame('2026-07-16 10:15:00', $task->skipped_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Tidak diperlukan untuk lokasi ini.', $task->skip_reason);
        $this->assertDatabaseHas('audit_logs', ['event' => 'OnboardingTask.skipped', 'auditable_id' => $task->id]);
        $this->actingAs($actor)
            ->get(route('hr.onboardings.show', [$onboarding, 'business_date' => '2026-07-16']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('onboarding.progress.percentage', 100)
                ->where('onboarding.tasks.0.skipped_by.id', $actor->id)
                ->where('onboarding.tasks.0.skip_reason', 'Tidak diperlukan untuk lokasi ini.'));
    }

    public function test_required_task_skip_needs_special_permission_and_reason(): void
    {
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate(required: true);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => 'Pengecualian disetujui.'])
            ->assertForbidden();
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => ''])
            ->assertSessionHasErrors('skip_reason');

        $actor->givePermissionTo('onboardings.task-skip-required');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => 'Pengecualian disetujui manajer.'])
            ->assertRedirect();

        $this->assertSame('SKIPPED', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_reopen_requires_reason_clears_terminal_evidence_and_reduces_progress(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 17)->setTime(8, 45));
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate(required: true, status: 'COMPLETED');
        $task->update([
            'completed_by_user_id' => $actor->id,
            'completed_at' => '2026-07-16 12:00:00',
            'completion_note' => 'Selesai sebelumnya.',
        ]);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.reopen', [$onboarding, $task]), ['reopen_reason' => ''])
            ->assertSessionHasErrors('reopen_reason');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.reopen', [$onboarding, $task]), ['reopen_reason' => 'Evidence perlu diperbaiki.'])
            ->assertRedirect();

        $task->refresh();
        $this->assertSame('PENDING', $task->status->value);
        $this->assertNull($task->completed_by_user_id);
        $this->assertNull($task->completed_at);
        $this->assertNull($task->completion_note);
        $this->assertSame($actor->id, $task->reopened_by_user_id);
        $this->assertSame('2026-07-17 08:45:00', $task->reopened_at?->format('Y-m-d H:i:s'));
        $this->assertSame('Evidence perlu diperbaiki.', $task->reopen_reason);
        $this->assertDatabaseHas('audit_logs', ['event' => 'OnboardingTask.reopened', 'auditable_id' => $task->id]);
    }

    public function test_invalid_and_repeated_transitions_do_not_change_state_or_duplicate_audit(): void
    {
        $actor = $this->actor();
        [$onboarding, $task] = $this->aggregate(required: false);

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.reopen', [$onboarding, $task]), ['reopen_reason' => 'Belum terminal.'])
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => 'Tidak diperlukan.'])
            ->assertRedirect();
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$onboarding, $task]), ['skip_reason' => 'Retry.'])
            ->assertSessionHasErrors('status');

        $this->assertSame('SKIPPED', $task->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_terminal_onboarding_and_unauthorized_user_cannot_skip_or_reopen(): void
    {
        $actor = $this->actor();
        $unauthorized = User::factory()->create();
        [$terminal, $terminalTask] = $this->aggregate(required: false, onboardingStatus: 'CANCELLED', employeeNumber: 'EMP-CANCELLED');
        [$active, $activeTask] = $this->aggregate(required: false, employeeNumber: 'EMP-UNAUTHORIZED');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.tasks.skip', [$terminal, $terminalTask]), ['skip_reason' => 'Tidak berlaku.'])
            ->assertSessionHasErrors('status');
        $this->actingAs($unauthorized)
            ->patch(route('hr.onboardings.tasks.skip', [$active, $activeTask]), ['skip_reason' => 'Tidak berizin.'])
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
    private function aggregate(bool $required, string $status = 'PENDING', string $onboardingStatus = 'IN_PROGRESS', string $employeeNumber = 'EMP-LIFECYCLE'): array
    {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $owner = User::factory()->create();
        $template = OnboardingTemplate::query()->create(['code' => "LIFE-{$employee->id}", 'name' => 'Lifecycle', 'active' => true]);
        $onboarding = Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-15',
            'status' => $onboardingStatus,
            'active_identity_key' => "employee:{$employee->id}:start:2026-07-15",
            'request_fingerprint' => hash('sha256', "lifecycle-{$employee->id}"),
        ]);
        $task = $onboarding->tasks()->create([
            'title' => 'Lifecycle task',
            'category' => 'HR',
            'required' => $required,
            'due_offset_days' => 0,
            'due_date' => '2026-07-15',
            'sort_order' => 0,
            'status' => $status,
        ]);

        return [$onboarding, $task];
    }
}
