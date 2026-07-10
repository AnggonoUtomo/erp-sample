<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HREmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach ([
            'hr.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.restore',
            'employees.force-delete',
            'employees.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.restore',
            'employees.force-delete',
            'employees.manage',
        ]);
    }

    public function test_authorized_users_can_view_employees(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.employees.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $refs = $this->references();

        $this->actingAs($user)
            ->post(route('hr.employees.store'), [
                'departement_id' => $refs['departement']->id,
                'position_id' => $refs['position']->id,
                'job_level_id' => $refs['jobLevel']->id,
                'work_location_id' => $refs['workLocation']->id,
                'employment_status_id' => $refs['employmentStatus']->id,
                'employment_type_id' => $refs['employmentType']->id,
                'employee_number' => 'EMP-1001',
                'first_name' => 'Raka',
                'last_name' => 'Wijaya',
                'display_name' => 'Raka Wijaya',
                'work_email' => 'raka.wijaya@company.test',
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'employee_number' => 'EMP-1001',
            'display_name' => 'Raka Wijaya',
            'departement_id' => $refs['departement']->id,
            'active' => true,
        ]);
    }

    public function test_authorized_users_can_update_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employee = $this->employee();
        $employmentStatus = EmploymentStatus::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('hr.employees.update', $employee), [
                'employment_status_id' => $employmentStatus->id,
                'employee_number' => 'EMP-2001',
                'first_name' => 'Updated',
                'display_name' => 'Updated Employee',
                'work_email' => 'updated.employee@company.test',
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'display_name' => 'Updated Employee',
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_soft_delete_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employee = $this->employee();

        $this->actingAs($user)
            ->delete(route('hr.employees.destroy', $employee))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_authorized_users_can_restore_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employee = $this->employee();
        $employee->delete();

        $this->actingAs($user)
            ->patch(route('hr.employees.restore', $employee->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_employee(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employee = $this->employee();
        $employee->delete();

        $this->actingAs($user)
            ->delete(route('hr.employees.force-destroy', $employee->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_employees', [
            'id' => $employee->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function references(): array
    {
        $departement = Departement::query()->create([
            'code' => 'HRD',
            'name' => 'Human Resources',
            'active' => true,
        ]);

        return [
            'departement' => $departement,
            'position' => Position::query()->create([
                'departement_id' => $departement->id,
                'code' => 'HR-MGR',
                'name' => 'HR Manager',
                'active' => true,
            ]),
            'jobLevel' => JobLevel::query()->create([
                'code' => 'L3',
                'name' => 'Manager',
                'active' => true,
            ]),
            'workLocation' => WorkLocation::query()->create([
                'code' => 'HQ-JKT',
                'name' => 'Head Office Jakarta',
                'timezone' => 'Asia/Jakarta',
                'active' => true,
            ]),
            'employmentStatus' => EmploymentStatus::query()->create([
                'code' => 'PERMANENT',
                'name' => 'Permanent',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'active' => true,
            ]),
            'employmentType' => EmploymentType::query()->create([
                'code' => 'PERMANENT',
                'name' => 'Permanent',
                'requires_contract_end_date' => false,
                'included_in_payroll' => true,
                'eligible_for_benefits' => true,
                'eligible_for_overtime' => true,
                'active' => true,
            ]),
        ];
    }

    private function employee(): Employee
    {
        $refs = $this->references();

        return Employee::query()->create([
            'departement_id' => $refs['departement']->id,
            'position_id' => $refs['position']->id,
            'job_level_id' => $refs['jobLevel']->id,
            'work_location_id' => $refs['workLocation']->id,
            'employment_status_id' => $refs['employmentStatus']->id,
            'employment_type_id' => $refs['employmentType']->id,
            'employee_number' => 'EMP-2001',
            'first_name' => 'Test',
            'display_name' => 'Test Employee',
            'active' => true,
        ]);
    }
}
