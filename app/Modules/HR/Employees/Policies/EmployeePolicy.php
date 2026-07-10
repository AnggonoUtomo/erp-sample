<?php

namespace App\Modules\HR\Employees\Policies;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'employees.view', 'employees.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employees.create', 'employees.manage']);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasAnyPermission(['employees.update', 'employees.manage']);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasAnyPermission(['employees.delete', 'employees.manage']);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->hasAnyPermission(['employees.restore', 'employees.manage']);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->hasAnyPermission(['employees.force-delete', 'employees.manage']);
    }
}
