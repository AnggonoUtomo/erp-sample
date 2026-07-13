<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
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
        foreach (['employee-movements.view', 'employee-movements.create', 'employee-movements.apply'] as $permission) {
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
        $status = EmploymentStatus::query()->create(['code' => 'ACTIVE', 'name' => 'Active', 'active' => true]);
        $type = EmploymentType::query()->create(['code' => 'PERM', 'name' => 'Permanent', 'active' => true]);
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-MOVE', 'first_name' => 'Move', 'display_name' => 'Move Employee',
            'departement_id' => $source->id, 'position_id' => $sourcePosition->id, 'work_location_id' => $sourceLocation->id,
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);

        return [$employee, $target, $targetPosition, $targetLocation];
    }
}
