<?php

namespace App\Modules\HR\OrganizationStructures\Database\Seeders;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HROrganizationStructureSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $company = $this->upsert(null, 'COMPANY', 'Company', 'company', null, 10);

        foreach (Departement::query()->whereNull('parent_id')->orderBy('sort_order')->get() as $departement) {
            $this->upsert($company->id, 'ORG-'.$departement->code, $departement->name, 'departement', $departement->id, $departement->sort_order);
        }
    }

    private function upsert(?int $parentId, string $code, string $name, string $nodeType, ?int $departementId, int $sortOrder): OrganizationStructure
    {
        $structure = OrganizationStructure::withTrashed()->updateOrCreate(
            ['code' => $code],
            ['parent_id' => $parentId, 'departement_id' => $departementId, 'position_id' => null, 'name' => $name, 'node_type' => $nodeType, 'active' => true, 'sort_order' => $sortOrder],
        );

        if ($structure->trashed()) {
            $structure->restore();
        }

        return $structure;
    }

    private function seedPermissions(): void
    {
        $permissions = ['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.delete', 'organization-structures.restore', 'organization-structures.force-delete', 'organization-structures.manage'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }
        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo(['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.restore']);
        Role::findOrCreate('hr-viewer')->givePermissionTo(['hr.view', 'organization-structures.view']);
    }
}
