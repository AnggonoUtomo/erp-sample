<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HRDepartementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['hr.view', 'departements.view', 'departements.create', 'departements.update', 'departements.delete', 'departements.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'departements.view',
            'departements.create',
            'departements.update',
            'departements.delete',
            'departements.manage',
        ]);
    }

    public function test_authorized_users_can_view_departements(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        Departement::query()->create([
            'code' => 'HRD',
            'name' => 'Human Resources',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('hr.departements.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_departement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.departements.store'), [
                'code' => 'FIN',
                'name' => 'Finance',
                'description' => 'Finance team',
                'active' => true,
                'sort_order' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_departements', [
            'code' => 'FIN',
            'name' => 'Finance',
            'active' => true,
        ]);
    }

    public function test_authorized_users_can_update_departement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $departement = Departement::query()->create([
            'code' => 'OPS',
            'name' => 'Operations',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.departements.update', $departement), [
                'code' => 'OPS',
                'name' => 'Operations Center',
                'description' => 'Updated',
                'active' => false,
                'sort_order' => 20,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_departements', [
            'id' => $departement->id,
            'name' => 'Operations Center',
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_delete_departement_without_children(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $departement = Departement::query()->create([
            'code' => 'TMP',
            'name' => 'Temporary',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('hr.departements.destroy', $departement))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_departements', [
            'id' => $departement->id,
        ]);
    }

    public function test_departement_with_children_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $parent = Departement::query()->create([
            'code' => 'HQ',
            'name' => 'Headquarters',
            'active' => true,
        ]);

        Departement::query()->create([
            'parent_id' => $parent->id,
            'code' => 'HRC',
            'name' => 'HR Child',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('hr.departements.destroy', $parent))
            ->assertStatus(422);

        $this->assertDatabaseHas('hr_departements', [
            'id' => $parent->id,
        ]);
    }
}
