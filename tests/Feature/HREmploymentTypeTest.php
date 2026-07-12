<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HREmploymentTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach ([
            'hr.view',
            'employment-types.view',
            'employment-types.create',
            'employment-types.update',
            'employment-types.delete',
            'employment-types.restore',
            'employment-types.force-delete',
            'employment-types.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'employment-types.view',
            'employment-types.create',
            'employment-types.update',
            'employment-types.delete',
            'employment-types.restore',
            'employment-types.force-delete',
            'employment-types.manage',
        ]);
    }

    public function test_authorized_users_can_view_employment_types(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.employment-types.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_employment_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.employment-types.store'), [
                'code' => 'PROBATION',
                'name' => 'Probation',
                'description' => 'Masa percobaan karyawan.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => true,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => true,
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_types', [
            'code' => 'PROBATION',
            'name' => 'Probation',
            'requires_contract_end_date' => true,
            'included_in_payroll' => true,
            'eligible_for_benefits' => false,
            'eligible_for_overtime' => true,
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_users_can_update_employment_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentType = EmploymentType::query()->create([
            'code' => 'INTERN',
            'name' => 'Intern',
            'requires_contract_end_date' => true,
            'included_in_payroll' => false,
            'eligible_for_benefits' => false,
            'eligible_for_overtime' => false,
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.employment-types.update', $employmentType), [
                'code' => 'INTERN',
                'name' => 'Internship',
                'description' => 'Updated',
                'requires_contract_end_date' => true,
                'included_in_payroll' => false,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => false,
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_types', [
            'id' => $employmentType->id,
            'name' => 'Internship',
            'included_in_payroll' => false,
            'eligible_for_overtime' => false,
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_soft_delete_employment_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentType = $this->employmentType();

        $this->actingAs($user)
            ->delete(route('hr.employment-types.destroy', $employmentType))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_employment_types', [
            'id' => $employmentType->id,
        ]);
    }

    public function test_authorized_users_can_restore_employment_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentType = $this->employmentType('RST');
        $employmentType->delete();

        $this->actingAs($user)
            ->patch(route('hr.employment-types.restore', $employmentType->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_employment_types', [
            'id' => $employmentType->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_employment_type(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $employmentType = $this->employmentType('DEL');
        $employmentType->delete();

        $this->actingAs($user)
            ->delete(route('hr.employment-types.force-destroy', $employmentType->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_employment_types', [
            'id' => $employmentType->id,
        ]);
    }

    public function test_users_without_permission_cannot_mutate_employment_types(): void
    {
        $user = User::factory()->create();
        $type = $this->employmentType('DENY');

        $this->actingAs($user)->post(route('hr.employment-types.store'))->assertForbidden();
        $this->actingAs($user)->put(route('hr.employment-types.update', $type))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.employment-types.destroy', $type))->assertForbidden();
        $type->delete();
        $this->actingAs($user)->patch(route('hr.employment-types.restore', $type->id))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.employment-types.force-destroy', $type->id))->assertForbidden();
        $this->assertSoftDeleted('hr_employment_types', ['id' => $type->id]);
    }

    private function employmentType(string $code = 'TMP'): EmploymentType
    {
        return EmploymentType::query()->create([
            'code' => $code,
            'name' => "{$code} Type",
            'requires_contract_end_date' => true,
            'included_in_payroll' => true,
            'eligible_for_benefits' => false,
            'eligible_for_overtime' => true,
            'active' => true,
        ]);
    }
}
