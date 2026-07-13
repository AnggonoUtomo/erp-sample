<?php

namespace Tests\Feature;

use App\Models\User;
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

        foreach (['hr.view', 'employee-contracts.view', 'employee-contracts.create'] as $permission) {
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
}
