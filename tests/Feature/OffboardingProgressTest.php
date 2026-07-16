<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('offboardings.view');
    }

    public function test_detail_returns_ordered_snapshot_tasks_and_deterministic_progress(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $offboarding = $this->offboarding();
        $offboarding->tasks()->createMany([
            $this->task('Optional selesai', false, 3, '2026-07-10', OffboardingTaskStatus::Completed),
            $this->task('Required terlambat', true, 1, '2026-07-14', OffboardingTaskStatus::Pending),
            $this->task('Optional mendatang', false, 2, '2026-07-20', OffboardingTaskStatus::InProgress),
            $this->task('Required selesai', true, 0, '2026-07-12', OffboardingTaskStatus::Skipped),
        ]);

        $this->actingAs($viewer)
            ->get(route('hr.offboardings.show', [
                'offboarding' => $offboarding,
                'business_date' => '2026-07-15',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/offboardings/show')
                ->where('businessDate', '2026-07-15')
                ->where('offboarding.progress.total', 4)
                ->where('offboarding.progress.terminal', 2)
                ->where('offboarding.progress.completed', 1)
                ->where('offboarding.progress.skipped', 1)
                ->where('offboarding.progress.required_incomplete', 1)
                ->where('offboarding.progress.optional_incomplete', 1)
                ->where('offboarding.progress.overdue', 1)
                ->where('offboarding.progress.percentage', 50)
                ->where('offboarding.tasks.0.title', 'Required selesai')
                ->where('offboarding.tasks.1.title', 'Required terlambat')
                ->where('offboarding.tasks.1.overdue', true)
                ->where('offboarding.tasks.2.title', 'Optional mendatang')
                ->where('offboarding.tasks.3.title', 'Optional selesai')
                ->where('offboarding.target_status.code', 'ENDED-PROGRESS')
                ->where('offboarding.exit_type', 'RESIGNATION')
                ->where('offboarding.exit_reason', 'Pengunduran diri.')
                ->where('offboarding.notes', 'Knowledge transfer.')
                ->missing('offboarding.active_identity_key')
                ->missing('offboarding.request_fingerprint')
                ->missing('offboarding.tasks.0.source_template_item_id'));
    }

    public function test_archived_detail_is_readable_and_empty_state_is_explicit(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $offboarding = $this->offboarding();
        $offboarding->delete();

        $this->actingAs($viewer)
            ->get(route('hr.offboardings.show', [
                'offboarding' => $offboarding->id,
                'business_date' => '2026-07-15',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('offboarding.archived', true)
                ->where('offboarding.progress.total', 0)
                ->where('offboarding.progress.percentage', 0)
                ->has('offboarding.tasks', 0));
    }

    public function test_detail_is_permission_protected_and_rejects_invalid_business_date(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $unauthorized = User::factory()->create();
        $offboarding = $this->offboarding();

        $this->actingAs($unauthorized)
            ->get(route('hr.offboardings.show', [
                'offboarding' => $offboarding,
                'business_date' => '2026-07-15',
            ]))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->from(route('hr.offboardings.index'))
            ->get(route('hr.offboardings.show', [
                'offboarding' => $offboarding,
                'business_date' => 'not-a-date',
            ]))
            ->assertRedirect(route('hr.offboardings.index'))
            ->assertSessionHasErrors('business_date');
    }

    private function offboarding(): Offboarding
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-OFF-PROGRESS',
            'first_name' => 'Uji',
            'display_name' => 'Uji Offboarding Progress',
            'active' => true,
        ]);
        $owner = User::factory()->create(['name' => 'Offboarding Owner']);
        $template = OffboardingTemplate::query()->create([
            'code' => 'OFF-PROGRESS',
            'name' => 'Offboarding Progress',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => 'ENDED-PROGRESS',
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);

        return Offboarding::query()->create([
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Pengunduran diri.',
            'notes' => 'Knowledge transfer.',
            'status' => 'DRAFT',
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "progress-{$employee->id}"),
        ]);
    }

    /** @return array<string, mixed> */
    private function task(
        string $title,
        bool $required,
        int $sortOrder,
        string $dueDate,
        OffboardingTaskStatus $status,
    ): array {
        return [
            'title' => $title,
            'category' => 'HR',
            'required' => $required,
            'due_offset_days' => 0,
            'due_date' => $dueDate,
            'sort_order' => $sortOrder,
            'status' => $status,
        ];
    }
}
