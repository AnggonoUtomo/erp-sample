<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContractsExpiringCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_lists_active_contracts_expiring_in_deterministic_inclusive_window(): void
    {
        [$employee, $type] = $this->references();
        $this->contract($employee, $type, 'EXP-TODAY', '2026-01-01', '2026-07-13');
        $this->contract($employee, $type, 'EXP-BOUNDARY', '2026-01-01', '2026-08-12');
        $this->contract($employee, $type, 'EXP-OUTSIDE', '2026-01-01', '2026-08-13');
        $this->contract($employee, $type, 'EXP-CANCELLED', '2026-01-01', '2026-07-20', 'CANCELLED');
        $archived = $this->contract($employee, $type, 'EXP-ARCHIVED', '2026-01-01', '2026-07-21');
        $archived->delete();

        $this->artisan('hr:contracts-expiring', ['--date' => '2026-07-13', '--within' => '30'])
            ->expectsOutputToContain('EXP-TODAY')
            ->expectsOutputToContain('EXP-BOUNDARY')
            ->doesntExpectOutputToContain('EXP-OUTSIDE')
            ->doesntExpectOutputToContain('EXP-CANCELLED')
            ->doesntExpectOutputToContain('EXP-ARCHIVED')
            ->expectsOutputToContain('2 contract(s) expiring from 2026-07-13 through 2026-08-12.')
            ->assertSuccessful();
    }

    public function test_command_is_read_only_and_returns_success_when_no_contract_matches(): void
    {
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, 'NOT-EXPIRING', '2026-01-01', '2027-12-31');
        $before = $contract->fresh()->getAttributes();

        $this->artisan('hr:contracts-expiring', ['--date' => '2026-07-13', '--within' => '7'])
            ->expectsOutputToContain('No contracts expiring from 2026-07-13 through 2026-07-20.')
            ->assertSuccessful();

        $this->assertSame($before, $contract->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_command_rejects_invalid_date_and_window(): void
    {
        $this->artisan('hr:contracts-expiring', ['--date' => '13-07-2026'])->assertFailed();
        $this->artisan('hr:contracts-expiring', ['--date' => '2026-07-13', '--within' => '-1'])->assertFailed();
    }

    public function test_expiry_list_filter_uses_the_same_explicit_window(): void
    {
        Permission::findOrCreate('employee-contracts.view');
        $user = User::factory()->create();
        $user->givePermissionTo('employee-contracts.view');
        [$employee, $type] = $this->references();
        $this->contract($employee, $type, 'LIST-IN-WINDOW', '2026-01-01', '2026-07-20');
        $this->contract($employee, $type, 'LIST-OUTSIDE', '2026-01-01', '2026-08-20');

        $this->actingAs($user)->get(route('hr.employee-contracts.index', [
            'expiry_date' => '2026-07-13', 'expiry_within' => 7,
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('filters.expiry_date', '2026-07-13')
            ->where('filters.expiry_within', 7)
            ->has('contracts.data', 1)
            ->where('contracts.data.0.contract_number', 'LIST-IN-WINDOW'));
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
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id,
            'employee_number' => 'EMP-EXPIRY', 'first_name' => 'Expiry',
            'display_name' => 'Expiry Employee', 'active' => true,
        ]);

        return [$employee, $type];
    }

    private function contract(Employee $employee, EmploymentType $type, string $number, string $start, string $end, string $status = 'ACTIVE'): EmployeeContract
    {
        return EmployeeContract::query()->create([
            'employee_id' => $employee->id, 'employment_type_id' => $type->id,
            'contract_number' => $number, 'start_date' => $start, 'end_date' => $end, 'status' => $status,
        ]);
    }
}
