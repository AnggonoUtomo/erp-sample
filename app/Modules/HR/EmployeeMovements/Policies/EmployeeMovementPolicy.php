<?php

namespace App\Modules\HR\EmployeeMovements\Policies;

use App\Models\User;

class EmployeeMovementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['employee-movements.view', 'employee-movements.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employee-movements.create', 'employee-movements.manage']);
    }

    public function apply(User $user): bool
    {
        return $user->hasAnyPermission(['employee-movements.apply', 'employee-movements.manage']);
    }
}
