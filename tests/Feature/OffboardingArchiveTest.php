<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['offboardings.view', 'offboardings.archive', 'offboardings.restore'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_list_filters_are_deterministic_paginated_and_can_scope_archived_history(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $target = $this->offboarding('EMP-OFF-FILTER', 'IN_PROGRESS', '2026-07-20', $viewer);
        $target->tasks()->create(['title' => 'Late handover', 'category' => 'HR', 'required' => true, 'due_offset_days' => -11, 'due_date' => '2026-07-09', 'sort_order' => 1, 'status' => 'PENDING']);
        $other = $this->offboarding('EMP-OFF-OTHER', 'COMPLETED', '2026-08-01');
        $other->tasks()->create(['title' => 'Future', 'category' => 'HR', 'required' => false, 'due_offset_days' => 2, 'due_date' => '2026-08-03', 'sort_order' => 1, 'status' => 'PENDING']);
        $archived = $this->offboarding('EMP-OFF-ARCHIVED', 'CANCELLED', '2026-07-15');
        $archived->delete();

        $this->actingAs($viewer)->get(route('hr.offboardings.index', [
            'employee_id' => $target->employee_id,
            'owner_user_id' => $viewer->id,
            'status' => 'IN_PROGRESS',
            'template_id' => $target->offboarding_template_id,
            'exit_type' => 'RESIGNATION',
            'exit_from' => '2026-07-20',
            'exit_to' => '2026-07-20',
            'overdue' => '1',
            'business_date' => '2026-07-10',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters.overdue', true)
            ->where('filters.archived', false)
            ->where('businessDate', '2026-07-10')
            ->has('offboardings.data', 1)
            ->where('offboardings.data.0.id', $target->id)
            ->where('offboardings.data.0.archived', false));

        $this->actingAs($viewer)->get(route('hr.offboardings.index', ['archived' => '1']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.archived', true)
                ->has('offboardings.data', 1)
                ->where('offboardings.data.0.id', $archived->id)
                ->where('offboardings.data.0.archived', true));
    }

    public function test_terminal_offboarding_can_be_archived_and_restored_without_force_delete(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo(['offboardings.archive', 'offboardings.restore']);
        $terminal = $this->offboarding('EMP-OFF-TERM', 'COMPLETED', '2026-07-01');
        $active = $this->offboarding('EMP-OFF-ACTIVE', 'READY_FOR_EXIT', '2026-07-02');

        $this->actingAs($actor)->delete(route('hr.offboardings.archive', $active))->assertSessionHasErrors('status');
        $this->actingAs($actor)->delete(route('hr.offboardings.archive', $terminal))->assertRedirect();
        $this->assertSoftDeleted('hr_offboardings', ['id' => $terminal->id]);

        $this->actingAs($actor)->patch(route('hr.offboardings.restore', $terminal->id))->assertRedirect();

        $this->assertDatabaseHas('hr_offboardings', ['id' => $terminal->id, 'deleted_at' => null, 'active_identity_key' => null]);
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('hr.offboardings.force-delete'));
        $this->assertDatabaseHas('audit_logs', ['event' => 'Offboarding.archived', 'auditable_id' => $terminal->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Offboarding.restored', 'auditable_id' => $terminal->id]);
    }

    public function test_archive_and_restore_are_permission_protected(): void
    {
        $actor = User::factory()->create();
        $terminal = $this->offboarding('EMP-OFF-DENIED', 'CANCELLED', '2026-07-01');

        $this->actingAs($actor)->delete(route('hr.offboardings.archive', $terminal))->assertForbidden();
        $terminal->delete();
        $this->actingAs($actor)->patch(route('hr.offboardings.restore', $terminal->id))->assertForbidden();
    }

    private function offboarding(string $employeeNumber, string $status, string $exitDate, ?User $owner = null): Offboarding
    {
        $activeStatus = EmploymentStatus::query()->create(['code' => 'ACTIVE-'.uniqid(), 'name' => 'Active', 'is_final_status' => false, 'active' => true]);
        $finalStatus = EmploymentStatus::query()->create(['code' => 'ENDED-'.uniqid(), 'name' => 'Ended', 'is_final_status' => true, 'active' => true]);
        $employee = Employee::query()->create([
            'employment_status_id' => $activeStatus->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Archive',
            'display_name' => $employeeNumber,
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create(['code' => 'PERM-'.uniqid(), 'name' => 'Permanent']);
        $contract = EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => 'OFF-ARCH-'.$employee->id,
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
        $template = OffboardingTemplate::query()->create(['code' => 'OFF-ARCH-'.uniqid(), 'name' => 'Archive template', 'active' => true]);
        $activeIdentity = in_array($status, ['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'], true) ? "employee:{$employee->id}" : null;

        return Offboarding::query()->create([
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $finalStatus->id,
            'owner_user_id' => ($owner ?? User::factory()->create())->id,
            'exit_date' => $exitDate,
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Approved exit.',
            'status' => $status,
            'active_identity_key' => $activeIdentity,
            'request_fingerprint' => hash('sha256', "archive-{$employee->id}"),
        ]);
    }
}
