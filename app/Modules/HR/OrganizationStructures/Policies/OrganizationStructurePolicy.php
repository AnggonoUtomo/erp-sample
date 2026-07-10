<?php

namespace App\Modules\HR\OrganizationStructures\Policies;

use App\Models\User;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;

class OrganizationStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'organization-structures.view', 'organization-structures.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['organization-structures.create', 'organization-structures.manage']);
    }

    public function update(User $user, OrganizationStructure $organizationStructure): bool
    {
        return $user->hasAnyPermission(['organization-structures.update', 'organization-structures.manage']);
    }

    public function delete(User $user, OrganizationStructure $organizationStructure): bool
    {
        return $user->hasAnyPermission(['organization-structures.delete', 'organization-structures.manage']);
    }

    public function restore(User $user, OrganizationStructure $organizationStructure): bool
    {
        return $user->hasAnyPermission(['organization-structures.restore', 'organization-structures.manage']);
    }

    public function forceDelete(User $user, OrganizationStructure $organizationStructure): bool
    {
        return $user->hasAnyPermission(['organization-structures.force-delete', 'organization-structures.manage']);
    }
}
