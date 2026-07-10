<?php

namespace App\Modules\HR\EmploymentTypes\Policies;

use App\Models\User;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;

class EmploymentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'employment-types.view', 'employment-types.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employment-types.create', 'employment-types.manage']);
    }

    public function update(User $user, EmploymentType $employmentType): bool
    {
        return $user->hasAnyPermission(['employment-types.update', 'employment-types.manage']);
    }

    public function delete(User $user, EmploymentType $employmentType): bool
    {
        return $user->hasAnyPermission(['employment-types.delete', 'employment-types.manage']);
    }

    public function restore(User $user, EmploymentType $employmentType): bool
    {
        return $user->hasAnyPermission(['employment-types.restore', 'employment-types.manage']);
    }

    public function forceDelete(User $user, EmploymentType $employmentType): bool
    {
        return $user->hasAnyPermission(['employment-types.force-delete', 'employment-types.manage']);
    }
}
