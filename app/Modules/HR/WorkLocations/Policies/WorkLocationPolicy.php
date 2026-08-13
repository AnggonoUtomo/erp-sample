<?php

namespace App\Modules\HR\WorkLocations\Policies;

use App\Models\User;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;

class WorkLocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'work-locations.view', 'work-locations.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['work-locations.create', 'work-locations.manage']);
    }

    public function update(User $user, WorkLocation $workLocation): bool
    {
        return $user->hasAnyPermission(['work-locations.update', 'work-locations.manage']);
    }

    public function delete(User $user, WorkLocation $workLocation): bool
    {
        return $user->hasAnyPermission(['work-locations.delete', 'work-locations.manage']);
    }

    public function restore(User $user, WorkLocation $workLocation): bool
    {
        return $user->hasAnyPermission(['work-locations.restore', 'work-locations.manage']);
    }

    public function forceDelete(User $user, WorkLocation $workLocation): bool
    {
        return $user->hasAnyPermission(['work-locations.force-delete', 'work-locations.manage']);
    }
}
