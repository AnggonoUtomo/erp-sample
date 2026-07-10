<?php

namespace App\Modules\HR\Positions\Policies;

use App\Models\User;
use App\Modules\HR\Positions\Models\Position;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'positions.view', 'positions.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['positions.create', 'positions.manage']);
    }

    public function update(User $user, Position $position): bool
    {
        return $user->hasAnyPermission(['positions.update', 'positions.manage']);
    }

    public function delete(User $user, Position $position): bool
    {
        return $user->hasAnyPermission(['positions.delete', 'positions.manage']);
    }
}
