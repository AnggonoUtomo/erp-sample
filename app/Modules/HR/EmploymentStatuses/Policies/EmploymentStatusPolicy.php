<?php

namespace App\Modules\HR\EmploymentStatuses\Policies;

use App\Models\User;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;

class EmploymentStatusPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'employment-statuses.view', 'employment-statuses.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employment-statuses.create', 'employment-statuses.manage']);
    }

    public function update(User $user, EmploymentStatus $employmentStatus): bool
    {
        return $user->hasAnyPermission(['employment-statuses.update', 'employment-statuses.manage']);
    }

    public function delete(User $user, EmploymentStatus $employmentStatus): bool
    {
        return $user->hasAnyPermission(['employment-statuses.delete', 'employment-statuses.manage']);
    }

    public function restore(User $user, EmploymentStatus $employmentStatus): bool
    {
        return $user->hasAnyPermission(['employment-statuses.restore', 'employment-statuses.manage']);
    }

    public function forceDelete(User $user, EmploymentStatus $employmentStatus): bool
    {
        return $user->hasAnyPermission(['employment-statuses.force-delete', 'employment-statuses.manage']);
    }
}
