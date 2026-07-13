<?php

namespace App\Modules\HR\EmployeeContracts\Policies;

use App\Models\User;

class EmployeeContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['employee-contracts.view', 'employee-contracts.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employee-contracts.create', 'employee-contracts.manage']);
    }

    public function activate(User $user): bool
    {
        return $user->hasAnyPermission(['employee-contracts.activate', 'employee-contracts.manage']);
    }
}
