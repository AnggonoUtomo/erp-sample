<?php

namespace App\Modules\Console\AccessControls\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class AccessControlPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $user->can('access-control.view');
    }

    public function create(User $user): bool
    {
        return $this->canManage($user) || $user->can('access-control.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $this->canManage($user) || $user->can('access-control.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $role->name !== 'super-admin' && ($this->canManage($user) || $user->can('access-control.delete'));
    }

    public function manage(User $user): bool
    {
        return $this->canManage($user);
    }

    private function canManage(User $user): bool
    {
        return $user->can('roles.manage');
    }
}
