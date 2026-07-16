<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeMovementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['employee-movements.view', 'employee-movements.create', 'employee-movements.apply', 'employee-movements.cancel'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_creates_transfer_draft_with_server_generated_history(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $user = User::factory()->create();
        $user->givePermissionTo('employee-movements.create');

        $this->actingAs($user)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id, 'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id, 'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id, 'reason' => 'Operational transfer',
        ])->assertRedirect();

        $movement = EmployeeMovement::query()->firstOrFail();
        $this->assertSame('DRAFT', $movement->status);
        $this->assertSame($employee->departement_id, $movement->before_values['departement_id']);
        $this->assertSame($targetDepartment->id, $movement->after_values['departement_id']);
        $this->assertSame($user->id, $movement->created_by);
    }

    public function test_apply_transfer_updates_employee_and_movement_atomically(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $creator = User::factory()->create();
        $creator->givePermissionTo('employee-movements.create');
        $this->actingAs($creator)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id, 'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id, 'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id, 'reason' => 'Transfer approved',
        ]);
        $movement = EmployeeMovement::query()->firstOrFail();
        $approver = User::factory()->create();
        $approver->givePermissionTo('employee-movements.apply');

        $this->actingAs($approver)->post(route('hr.employee-movements.apply', $movement))->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id, 'departement_id' => $targetDepartment->id,
            'position_id' => $targetPosition->id, 'work_location_id' => $targetLocation->id,
        ]);
        $this->assertDatabaseHas('hr_employee_movements', ['id' => $movement->id, 'status' => 'APPLIED', 'applied_by' => $approver->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'EmployeeMovement.applied', 'auditable_id' => $movement->id]);
        $this->actingAs($approver)->post(route('hr.employee-movements.apply', $movement))->assertSessionHasErrors('status');
    }

    public function test_promotion_draft_and_apply_updates_job_level_atomically(): void
    {
        [$employee, , , , $targetJobLevel] = $this->references();
        $creator = User::factory()->create();
        $creator->givePermissionTo(['employee-movements.create', 'employee-movements.apply']);

        $this->actingAs($creator)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'type' => 'PROMOTION',
            'effective_date' => now()->toDateString(),
            'job_level_id' => $targetJobLevel->id,
            'reason' => 'Promotion approved',
        ])->assertRedirect();

        $movement = EmployeeMovement::query()->firstOrFail();
        $this->assertSame('PROMOTION', $movement->type);
        $this->assertSame($employee->job_level_id, $movement->before_values['job_level_id']);
        $this->assertSame($targetJobLevel->id, $movement->after_values['job_level_id']);

        $this->actingAs($creator)->post(route('hr.employee-movements.apply', $movement))->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'job_level_id' => $targetJobLevel->id,
        ]);
        $this->assertDatabaseHas('hr_employee_movements', [
            'id' => $movement->id,
            'type' => 'PROMOTION',
            'status' => 'APPLIED',
        ]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'EmployeeMovement.applied', 'auditable_id' => $movement->id]);
    }

    public function test_promotion_and_demotion_require_job_level_change(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $authorized = User::factory()->create();
        $authorized->givePermissionTo('employee-movements.create');

        $this->actingAs($authorized)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'type' => 'PROMOTION',
            'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id,
            'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id,
            'reason' => 'Invalid promotion without job level',
        ])->assertSessionHasErrors('job_level_id');

        $this->assertDatabaseCount('hr_employee_movements', 0);
    }

    public function test_employment_change_draft_and_apply_updates_status_and_type_with_contract_coordination(): void
    {
        [$employee, , , , , $targetStatus, $targetType] = $this->references();
        $creator = User::factory()->create();
        $creator->givePermissionTo(['employee-movements.create', 'employee-movements.apply']);
        EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $targetType->id,
            'contract_number' => 'MOVE-CTR-001',
            'start_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($creator)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'type' => 'EMPLOYMENT_CHANGE',
            'effective_date' => now()->toDateString(),
            'employment_status_id' => $targetStatus->id,
            'employment_type_id' => $targetType->id,
            'reason' => 'Employment profile updated from contract',
        ])->assertRedirect();

        $movement = EmployeeMovement::query()->firstOrFail();
        $this->assertSame('EMPLOYMENT_CHANGE', $movement->type);
        $this->assertSame($employee->employment_status_id, $movement->before_values['employment_status_id']);
        $this->assertSame($targetStatus->id, $movement->after_values['employment_status_id']);
        $this->assertSame($employee->employment_type_id, $movement->before_values['employment_type_id']);
        $this->assertSame($targetType->id, $movement->after_values['employment_type_id']);

        $this->actingAs($creator)->post(route('hr.employee-movements.apply', $movement))->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'employment_status_id' => $targetStatus->id,
            'employment_type_id' => $targetType->id,
        ]);
        $this->assertDatabaseHas('hr_employee_movements', ['id' => $movement->id, 'status' => 'APPLIED']);
    }

    public function test_employment_type_change_requires_matching_effective_contract(): void
    {
        [$employee, , , , , $targetStatus, $targetType] = $this->references();
        $authorized = User::factory()->create();
        $authorized->givePermissionTo('employee-movements.create');

        $this->actingAs($authorized)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'type' => 'EMPLOYMENT_CHANGE',
            'effective_date' => now()->toDateString(),
            'employment_status_id' => $targetStatus->id,
            'employment_type_id' => $targetType->id,
            'reason' => 'Missing matching contract',
        ])->assertSessionHasErrors('employment_type_id');

        $this->assertDatabaseCount('hr_employee_movements', 0);
    }

    public function test_apply_rejects_stale_profile_and_keeps_draft(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-movements.create', 'employee-movements.apply']);
        $this->actingAs($user)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id, 'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id, 'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id, 'reason' => 'Will become stale',
        ]);
        $movement = EmployeeMovement::query()->firstOrFail();
        $employee->update(['work_location_id' => $targetLocation->id]);

        $this->actingAs($user)->post(route('hr.employee-movements.apply', $movement))->assertSessionHasErrors('profile');
        $this->assertDatabaseHas('hr_employee_movements', ['id' => $movement->id, 'status' => 'DRAFT', 'applied_at' => null]);
    }

    public function test_future_effective_draft_waits_until_due_date_and_can_be_cancelled(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-movements.create', 'employee-movements.apply', 'employee-movements.cancel']);

        $this->actingAs($user)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'effective_date' => now()->addDay()->toDateString(),
            'departement_id' => $targetDepartment->id,
            'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id,
            'reason' => 'Future transfer',
        ])->assertRedirect();

        $movement = EmployeeMovement::query()->firstOrFail();
        $this->actingAs($user)->post(route('hr.employee-movements.apply', $movement))->assertSessionHasErrors('effective_date');

        $this->actingAs($user)->post(route('hr.employee-movements.cancel', $movement), [
            'reason' => 'Business request withdrawn',
        ])->assertRedirect();

        $this->assertDatabaseHas('hr_employee_movements', [
            'id' => $movement->id,
            'status' => 'CANCELLED',
            'cancelled_by' => $user->id,
            'cancel_reason' => 'Business request withdrawn',
        ]);
        $this->actingAs($user)->post(route('hr.employee-movements.apply', $movement))->assertSessionHasErrors('status');
    }

    public function test_due_command_dry_runs_and_applies_due_movements_only(): void
    {
        [$employee, $targetDepartment, $targetPosition, $targetLocation] = $this->references();
        $creator = User::factory()->create();
        $creator->givePermissionTo('employee-movements.create');

        $this->actingAs($creator)->post(route('hr.employee-movements.store'), [
            'employee_id' => $employee->id,
            'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id,
            'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id,
            'reason' => 'Due transfer',
        ])->assertRedirect();

        $dueMovement = EmployeeMovement::query()->firstOrFail();
        $this->artisan('hr:employee-movements:apply-due', ['--date' => now()->toDateString(), '--dry-run' => true])
            ->assertSuccessful();
        $this->assertDatabaseHas('hr_employee_movements', ['id' => $dueMovement->id, 'status' => 'DRAFT']);

        $this->artisan('hr:employee-movements:apply-due', ['--date' => now()->toDateString()])
            ->assertSuccessful();

        $this->assertDatabaseHas('hr_employee_movements', ['id' => $dueMovement->id, 'status' => 'APPLIED']);
        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'departement_id' => $targetDepartment->id,
            'position_id' => $targetPosition->id,
            'work_location_id' => $targetLocation->id,
        ]);
    }

    public function test_invalid_transfer_and_unauthorized_mutations_are_rejected(): void
    {
        [$employee, $targetDepartment, , $targetLocation] = $this->references();
        $wrongPosition = Position::query()->where('departement_id', $employee->departement_id)->firstOrFail();
        $authorized = User::factory()->create();
        $authorized->givePermissionTo('employee-movements.create');
        $payload = [
            'employee_id' => $employee->id, 'effective_date' => now()->toDateString(),
            'departement_id' => $targetDepartment->id, 'position_id' => $wrongPosition->id,
            'work_location_id' => $targetLocation->id, 'supervisor_id' => $employee->id, 'reason' => 'Invalid',
        ];

        $this->actingAs($authorized)->post(route('hr.employee-movements.store'), $payload)
            ->assertSessionHasErrors(['position_id', 'supervisor_id']);
        $this->actingAs($authorized)->post(route('hr.employee-movements.store'), [
            ...$payload,
            'effective_date' => now()->subDay()->toDateString(),
        ])->assertSessionHasErrors('effective_date');
        $this->actingAs(User::factory()->create())->post(route('hr.employee-movements.store'), $payload)->assertForbidden();
    }

    private function references(): array
    {
        $source = Departement::query()->create(['code' => 'SRC', 'name' => 'Source', 'active' => true]);
        $target = Departement::query()->create(['code' => 'DST', 'name' => 'Destination', 'active' => true]);
        $sourcePosition = Position::query()->create(['departement_id' => $source->id, 'code' => 'SRC-P', 'name' => 'Source Position', 'active' => true]);
        $targetPosition = Position::query()->create(['departement_id' => $target->id, 'code' => 'DST-P', 'name' => 'Target Position', 'active' => true]);
        $sourceLocation = WorkLocation::query()->create(['code' => 'SRC-L', 'name' => 'Source Location', 'timezone' => 'Asia/Jakarta', 'active' => true]);
        $targetLocation = WorkLocation::query()->create(['code' => 'DST-L', 'name' => 'Target Location', 'timezone' => 'Asia/Jakarta', 'active' => true]);
        $sourceJobLevel = JobLevel::query()->create(['code' => 'L1', 'name' => 'Level 1', 'active' => true, 'sort_order' => 1]);
        $targetJobLevel = JobLevel::query()->create(['code' => 'L2', 'name' => 'Level 2', 'active' => true, 'sort_order' => 2]);
        $status = EmploymentStatus::query()->create(['code' => 'ACTIVE', 'name' => 'Active', 'active' => true]);
        $targetStatus = EmploymentStatus::query()->create(['code' => 'PROBATION', 'name' => 'Probation', 'active' => true]);
        $type = EmploymentType::query()->create(['code' => 'PERM', 'name' => 'Permanent', 'active' => true]);
        $targetType = EmploymentType::query()->create(['code' => 'CONTRACT', 'name' => 'Contract', 'active' => true]);
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-MOVE', 'first_name' => 'Move', 'display_name' => 'Move Employee',
            'departement_id' => $source->id, 'position_id' => $sourcePosition->id, 'work_location_id' => $sourceLocation->id,
            'job_level_id' => $sourceJobLevel->id,
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);

        return [$employee, $target, $targetPosition, $targetLocation, $targetJobLevel, $targetStatus, $targetType];
    }
}
