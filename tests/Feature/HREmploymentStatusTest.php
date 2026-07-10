<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HREmploymentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach ([
            'hr.view',
            'employment-statuses.view',
            'employment-statuses.create',
            'employment-statuses.update',
            'employment-statuses.delete',
            'employment-statuses.restore',
            'employment-statuses.force-delete',
            'employment-statuses.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'employment-statuses.view',
            'employment-statuses.create',
            'employment-statuses.update',
            'employment-statuses.delete',
            'employment-statuses.restore',
            'employment-statuses.force-delete',
            'employment-statuses.manage',
        ]);
    }

    public function test_authorized_users_can_view_employment_statuses(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.employment-statuses.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_employment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.employment-statuses.store'), [
                'code' => 'PROBATION',
                'name' => 'Probation',
                'description' => 'Masa percobaan karyawan.',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_statuses', [
            'code' => 'PROBATION',
            'name' => 'Probation',
            'requires_attendance' => true,
            'included_in_payroll' => true,
            'is_final_status' => false,
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_users_can_update_employment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentStatus = EmploymentStatus::query()->create([
            'code' => 'INTERN',
            'name' => 'Intern',
            'requires_attendance' => true,
            'included_in_payroll' => false,
            'is_final_status' => false,
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.employment-statuses.update', $employmentStatus), [
                'code' => 'INTERN',
                'name' => 'Internship',
                'description' => 'Updated',
                'requires_attendance' => true,
                'included_in_payroll' => false,
                'is_final_status' => false,
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_statuses', [
            'id' => $employmentStatus->id,
            'name' => 'Internship',
            'included_in_payroll' => false,
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_soft_delete_employment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentStatus = $this->employmentStatus();

        $this->actingAs($user)
            ->delete(route('hr.employment-statuses.destroy', $employmentStatus))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_employment_statuses', [
            'id' => $employmentStatus->id,
        ]);
    }

    public function test_authorized_users_can_restore_employment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentStatus = $this->employmentStatus('RST');
        $employmentStatus->delete();

        $this->actingAs($user)
            ->patch(route('hr.employment-statuses.restore', $employmentStatus->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_statuses', [
            'id' => $employmentStatus->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_employment_status(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentStatus = $this->employmentStatus('DEL');
        $employmentStatus->delete();

        $this->actingAs($user)
            ->delete(route('hr.employment-statuses.force-destroy', $employmentStatus->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_employment_statuses', [
            'id' => $employmentStatus->id,
        ]);
    }

    private function employmentStatus(string $code = 'TMP'): EmploymentStatus
    {
        return EmploymentStatus::query()->create([
            'code' => $code,
            'name' => "{$code} Status",
            'requires_attendance' => true,
            'included_in_payroll' => true,
            'is_final_status' => false,
            'active' => true,
        ]);
    }
}
