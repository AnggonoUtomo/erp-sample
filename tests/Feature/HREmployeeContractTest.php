<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        foreach (['hr.view', 'employee-contracts.view', 'employee-contracts.create', 'employee-contracts.activate', 'employee-contracts.terminate', 'employee-contracts.cancel', 'employee-contracts.supersede'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_can_view_and_create_draft_contract(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['hr.view', 'employee-contracts.view', 'employee-contracts.create']);
        [$employee, $type] = $this->references();

        $this->actingAs($user)->get(route('hr.employee-contracts.index'))->assertOk();

        $this->actingAs($user)->post(route('hr.employee-contracts.store'), [
            'employee_id' => $employee->id,
            'employment_type_id' => $type->id,
            'contract_number' => 'CTR-2026-001',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'notes' => 'Initial fixed-term contract',
        ])->assertRedirect();

        $this->assertDatabaseHas('hr_employee_contracts', [
            'employee_id' => $employee->id,
            'contract_number' => 'CTR-2026-001',
            'status' => 'DRAFT',
        ]);
    }

    public function test_contract_periods_cannot_overlap(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-contracts.create']);
        [$employee, $type] = $this->references();

        $payload = [
            'employee_id' => $employee->id,
            'employment_type_id' => $type->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ];

        $this->actingAs($user)->post(route('hr.employee-contracts.store'), [
            ...$payload,
            'contract_number' => 'CTR-001',
        ])->assertRedirect();

        $this->actingAs($user)->post(route('hr.employee-contracts.store'), [
            ...$payload,
            'contract_number' => 'CTR-002',
            'start_date' => '2026-06-01',
        ])->assertSessionHasErrors('start_date');
    }

    public function test_fixed_term_contract_requires_end_date(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-contracts.create']);
        [$employee, $type] = $this->references();

        $this->actingAs($user)->post(route('hr.employee-contracts.store'), [
            'employee_id' => $employee->id,
            'employment_type_id' => $type->id,
            'contract_number' => 'CTR-003',
            'start_date' => '2026-01-01',
        ])->assertSessionHasErrors('end_date');
    }

    public function test_user_without_permission_cannot_mutate_contracts(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('hr.employee-contracts.store'))->assertForbidden();
    }

    public function test_effective_contract_query_uses_inclusive_boundaries_and_ignores_cancelled_or_archived(): void
    {
        [$employee, $type] = $this->references();
        $active = $this->contract($employee, $type, 'ACTIVE-001', '2026-01-01', '2026-12-31');
        $this->contract($employee, $type, 'CANCELLED-001', '2026-01-01', '2026-12-31', 'CANCELLED');
        $archived = $this->contract($employee, $type, 'ARCHIVED-001', '2026-01-01', '2026-12-31');
        $archived->delete();

        $this->assertSame([$active->id], EmployeeContract::query()->effectiveOn('2026-01-01')->pluck('id')->all());
        $this->assertSame([$active->id], EmployeeContract::query()->effectiveOn('2026-12-31')->pluck('id')->all());
        $this->assertFalse(EmployeeContract::query()->effectiveOn('2027-01-01')->exists());
    }

    public function test_open_ended_contract_overlaps_every_later_period(): void
    {
        [$employee, $type] = $this->references();
        $this->contract($employee, $type, 'OPEN-001', '2026-01-01', null);

        $this->assertTrue(EmployeeContract::query()->forEmployee($employee->id)->overlapping('2030-01-01', '2030-12-31')->exists());
        $this->assertFalse(EmployeeContract::query()->forEmployee($employee->id)->overlapping('2025-01-01', '2025-12-31')->exists());
    }

    public function test_authorized_user_can_activate_draft_contract_idempotently(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.activate');
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'DRAFT-001', '2026-01-01', '2026-12-31', 'DRAFT');

        $this->actingAs($user)->post(route('hr.employee-contracts.activate', $contract))->assertRedirect();
        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $contract->id, 'status' => 'ACTIVE']);
        $this->assertDatabaseCount('audit_logs', 1);

        $this->actingAs($user)->post(route('hr.employee-contracts.activate', $contract))->assertRedirect();
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_activation_rejects_overlapping_contract(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.activate');
        [$employee, $type] = $this->references();
        $this->contract($employee, $type, 'ACTIVE-OVERLAP', '2026-01-01', '2026-12-31');
        $draft = $this->contract($employee, $type, 'DRAFT-OVERLAP', '2026-06-01', '2027-05-31', 'DRAFT');

        $this->actingAs($user)->post(route('hr.employee-contracts.activate', $draft))
            ->assertSessionHasErrors('start_date');
        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $draft->id, 'status' => 'DRAFT']);
    }

    public function test_user_without_activate_permission_cannot_activate_contract(): void
    {
        $user = User::factory()->create();
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'DRAFT-DENIED', '2026-01-01', '2026-12-31', 'DRAFT');

        $this->actingAs($user)->post(route('hr.employee-contracts.activate', $contract))->assertForbidden();
    }

    public function test_authorized_user_can_terminate_active_contract_with_reason(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.terminate');
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'ACTIVE-END', '2026-01-01', '2026-12-31');

        $this->actingAs($user)->post(route('hr.employee-contracts.terminate', $contract), [
            'end_date' => '2026-06-30', 'reason' => 'Mutual agreement',
        ])->assertRedirect();

        $this->assertDatabaseHas('hr_employee_contracts', [
            'id' => $contract->id, 'status' => 'ENDED', 'end_date' => '2026-06-30 00:00:00',
            'ended_reason' => 'Mutual agreement',
        ]);
    }

    public function test_terminate_rejects_missing_reason_invalid_date_and_non_active_state(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.terminate');
        [$employee, $type] = $this->references();
        $active = $this->contract($employee, $type, 'ACTIVE-INVALID', '2026-01-01', '2026-12-31');
        $draft = $this->contract($employee, $type, 'DRAFT-INVALID', '2027-01-01', '2027-12-31', 'DRAFT');

        $this->actingAs($user)->post(route('hr.employee-contracts.terminate', $active), ['end_date' => '2025-12-31'])
            ->assertSessionHasErrors(['end_date', 'reason']);
        $this->actingAs($user)->post(route('hr.employee-contracts.terminate', $draft), ['end_date' => '2027-06-30', 'reason' => 'Invalid'])
            ->assertSessionHasErrors('status');
    }

    public function test_authorized_user_can_cancel_draft_contract_but_cannot_repeat_transition(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.cancel');
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'DRAFT-CANCEL', '2026-01-01', '2026-12-31', 'DRAFT');

        $this->actingAs($user)->post(route('hr.employee-contracts.cancel', $contract), ['reason' => 'Created in error'])
            ->assertRedirect();
        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $contract->id, 'status' => 'CANCELLED', 'ended_reason' => 'Created in error']);

        $this->actingAs($user)->post(route('hr.employee-contracts.cancel', $contract), ['reason' => 'Again'])
            ->assertSessionHasErrors('status');
    }

    public function test_user_without_lifecycle_permissions_cannot_terminate_or_cancel(): void
    {
        $user = User::factory()->create();
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'ACTIVE-DENIED', '2026-01-01', '2026-12-31');

        $this->actingAs($user)->post(route('hr.employee-contracts.terminate', $contract))->assertForbidden();
        $this->actingAs($user)->post(route('hr.employee-contracts.cancel', $contract))->assertForbidden();
    }

    public function test_authorized_user_can_supersede_active_contract_atomically(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.supersede');
        [$employee, $type] = $this->references();
        $old = $this->contract($employee, $type, 'OLD-001', '2026-01-01', null);

        $this->actingAs($user)->post(route('hr.employee-contracts.supersede', $old), [
            'employment_type_id' => $type->id, 'contract_number' => 'NEW-001',
            'start_date' => '2027-01-01', 'end_date' => '2027-12-31',
            'reason' => 'Annual renewal',
        ])->assertRedirect();

        $replacement = EmployeeContract::query()->where('contract_number', 'NEW-001')->firstOrFail();
        $this->assertDatabaseHas('hr_employee_contracts', [
            'id' => $old->id, 'status' => 'ENDED', 'end_date' => '2026-12-31 00:00:00',
            'superseded_by_id' => $replacement->id, 'ended_reason' => 'Annual renewal',
        ]);
        $this->assertSame('ACTIVE', $replacement->status);
        $this->assertSame($employee->id, $replacement->employee_id);
    }

    public function test_failed_supersede_rolls_back_old_contract(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.supersede');
        [$employee, $type] = $this->references();
        $old = $this->contract($employee, $type, 'OLD-ROLLBACK', '2026-01-01', null);
        $this->contract($employee, $type, 'DUPLICATE-NUMBER', '2025-01-01', '2025-12-31', 'ENDED');

        $this->actingAs($user)->post(route('hr.employee-contracts.supersede', $old), [
            'employment_type_id' => $type->id, 'contract_number' => 'DUPLICATE-NUMBER',
            'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'reason' => 'Should fail',
        ])->assertSessionHasErrors('contract_number');

        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $old->id, 'status' => 'ACTIVE', 'end_date' => null, 'superseded_by_id' => null]);
    }

    public function test_supersede_rejects_non_active_contract_and_unauthorized_user(): void
    {
        [$employee, $type] = $this->references();
        $draft = $this->contract($employee, $type, 'DRAFT-SUPERSEDE', '2026-01-01', '2026-12-31', 'DRAFT');
        $payload = ['employment_type_id' => $type->id, 'contract_number' => 'NEW-DENIED', 'start_date' => '2027-01-01', 'end_date' => '2027-12-31', 'reason' => 'Renewal'];

        $authorized = User::factory()->create();
        $authorized->givePermissionTo('employee-contracts.supersede');
        $this->actingAs($authorized)->post(route('hr.employee-contracts.supersede', $draft), $payload)->assertSessionHasErrors('status');

        $this->actingAs(User::factory()->create())->post(route('hr.employee-contracts.supersede', $draft), $payload)->assertForbidden();
    }

    /** @return array{Employee, EmploymentType} */
    private function references(): array
    {
        $status = EmploymentStatus::query()->create([
            'code' => 'ACTIVE', 'name' => 'Active', 'requires_attendance' => true,
            'included_in_payroll' => true, 'is_final_status' => false, 'active' => true,
        ]);
        $type = EmploymentType::query()->create([
            'code' => 'FIXED_TERM', 'name' => 'Fixed Term', 'requires_contract_end_date' => true,
            'included_in_payroll' => true, 'eligible_for_benefits' => true,
            'eligible_for_overtime' => true, 'active' => true,
        ]);
        $employee = Employee::query()->create([
            'employment_status_id' => $status->id,
            'employment_type_id' => $type->id,
            'employee_number' => 'EMP-C001', 'first_name' => 'Contract',
            'display_name' => 'Contract Employee', 'active' => true,
        ]);

        return [$employee, $type];
    }

    private function contract(Employee $employee, EmploymentType $type, string $number, string $start, ?string $end, string $status = 'ACTIVE'): EmployeeContract
    {
        return EmployeeContract::query()->create([
            'employee_id' => $employee->id, 'employment_type_id' => $type->id,
            'contract_number' => $number, 'start_date' => $start, 'end_date' => $end, 'status' => $status,
        ]);
    }
}
