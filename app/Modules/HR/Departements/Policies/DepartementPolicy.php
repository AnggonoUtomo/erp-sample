<?php

namespace App\Modules\HR\Departements\Policies;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;

class DepartementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'departements.view', 'departements.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['departements.create', 'departements.manage']);
    }

    public function update(User $user, Departement $departement): bool
    {
        return $user->hasAnyPermission(['departements.update', 'departements.manage']);
    }

    public function delete(User $user, Departement $departement): bool
    {
        return $user->hasAnyPermission(['departements.delete', 'departements.manage']);
    }
}
