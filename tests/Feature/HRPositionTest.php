<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HRPositionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['hr.view', 'positions.view', 'positions.create', 'positions.update', 'positions.delete', 'positions.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'positions.view',
            'positions.create',
            'positions.update',
            'positions.delete',
            'positions.manage',
        ]);
    }

    public function test_authorized_users_can_view_positions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.positions.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_position(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $departement = $this->departement();

        $this->actingAs($user)
            ->post(route('hr.positions.store'), [
                'departement_id' => $departement->id,
                'code' => 'HR-MGR',
                'name' => 'HR Manager',
                'description' => 'People operation lead',
                'active' => true,
                'sort_order' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_positions', [
            'departement_id' => $departement->id,
            'code' => 'HR-MGR',
            'name' => 'HR Manager',
            'active' => true,
        ]);
    }

    public function test_authorized_users_can_update_position(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $departement = $this->departement();
        $position = Position::query()->create([
            'departement_id' => $departement->id,
            'code' => 'OPS-SPV',
            'name' => 'Operations Supervisor',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.positions.update', $position), [
                'departement_id' => $departement->id,
                'code' => 'OPS-SPV',
                'name' => 'Operations Lead',
                'description' => 'Updated',
                'active' => false,
                'sort_order' => 20,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_positions', [
            'id' => $position->id,
            'name' => 'Operations Lead',
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_delete_position(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $departement = $this->departement();
        $position = Position::query()->create([
            'departement_id' => $departement->id,
            'code' => 'TMP',
            'name' => 'Temporary Position',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('hr.positions.destroy', $position))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_positions', [
            'id' => $position->id,
        ]);
    }

    public function test_position_requires_active_departement(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $departement = $this->departement();
        $departement->delete();

        $this->actingAs($user)
            ->post(route('hr.positions.store'), [
                'departement_id' => $departement->id,
                'code' => 'BAD',
                'name' => 'Invalid Position',
                'active' => true,
            ])
            ->assertSessionHasErrors('departement_id');
    }

    private function departement(): Departement
    {
        return Departement::query()->create([
            'code' => 'HRD',
            'name' => 'Human Resources',
            'active' => true,
        ]);
    }
}
