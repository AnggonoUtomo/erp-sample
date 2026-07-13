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

    public function terminate(User $user): bool
    {
        return $user->hasAnyPermission(['employee-contracts.terminate', 'employee-contracts.manage']);
    }

    public function cancel(User $user): bool
    {
        return $user->hasAnyPermission(['employee-contracts.cancel', 'employee-contracts.manage']);
    }
}
