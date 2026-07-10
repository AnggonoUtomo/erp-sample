<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HROrganizationStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.delete', 'organization-structures.restore', 'organization-structures.force-delete', 'organization-structures.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
        Role::findOrCreate('admin')->syncPermissions(['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.delete', 'organization-structures.restore', 'organization-structures.force-delete', 'organization-structures.manage']);
    }

    public function test_authorized_users_can_view_organization_structures(): void
    {
        $this->actingAs($this->user())->get(route('hr.organization-structures.index'))->assertOk();
    }

    public function test_authorized_users_can_create_organization_structure(): void
    {
        $departement = Departement::query()->create(['code' => 'HRD', 'name' => 'Human Resources', 'active' => true]);
        $this->actingAs($this->user())->post(route('hr.organization-structures.store'), [
            'departement_id' => $departement->id,
            'code' => 'ORG-HRD',
            'name' => 'Human Resources',
            'node_type' => 'departement',
            'active' => true,
        ])->assertRedirect();
        $this->assertDatabaseHas('hr_organization_structures', ['code' => 'ORG-HRD', 'departement_id' => $departement->id, 'sort_order' => 1]);
    }

    public function test_authorized_users_can_update_organization_structure(): void
    {
        $structure = $this->structure('ORG-OPS');
        $this->actingAs($this->user())->put(route('hr.organization-structures.update', $structure), [
            'code' => 'ORG-OPS',
            'name' => 'Operations Updated',
            'node_type' => 'division',
            'active' => false,
        ])->assertRedirect();
        $this->assertDatabaseHas('hr_organization_structures', ['id' => $structure->id, 'name' => 'Operations Updated', 'active' => false]);
    }

    public function test_structure_with_active_children_cannot_be_deleted(): void
    {
        $parent = $this->structure('PARENT');
        $this->structure('CHILD', $parent->id);
        $this->actingAs($this->user())->delete(route('hr.organization-structures.destroy', $parent))->assertSessionHasErrors('organization_structure');
        $this->assertDatabaseHas('hr_organization_structures', ['id' => $parent->id, 'deleted_at' => null]);
    }

    public function test_authorized_users_can_soft_delete_organization_structure(): void
    {
        $structure = $this->structure('DEL-SOFT');
        $this->actingAs($this->user())->delete(route('hr.organization-structures.destroy', $structure))->assertRedirect();
        $this->assertSoftDeleted('hr_organization_structures', ['id' => $structure->id]);
    }

    public function test_authorized_users_can_restore_organization_structure(): void
    {
        $structure = $this->structure('RESTORE');
        $structure->delete();
        $this->actingAs($this->user())->patch(route('hr.organization-structures.restore', $structure->id))->assertRedirect();
        $this->assertDatabaseHas('hr_organization_structures', ['id' => $structure->id, 'deleted_at' => null]);
    }

    public function test_authorized_users_can_force_delete_organization_structure(): void
    {
        $structure = $this->structure('FORCE');
        $structure->delete();
        $this->actingAs($this->user())->delete(route('hr.organization-structures.force-destroy', $structure->id))->assertRedirect();
        $this->assertDatabaseMissing('hr_organization_structures', ['id' => $structure->id]);
    }

    private function user(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    private function structure(string $code, ?int $parentId = null): OrganizationStructure
    {
        return OrganizationStructure::query()->create(['parent_id' => $parentId, 'code' => $code, 'name' => "{$code} Structure", 'node_type' => 'unit', 'active' => true]);
    }
}
