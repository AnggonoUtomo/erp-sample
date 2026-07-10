<?php

namespace App\Modules\HR\JobLevels\Policies;

use App\Models\User;
use App\Modules\HR\JobLevels\Models\JobLevel;

class JobLevelPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'job-levels.view', 'job-levels.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['job-levels.create', 'job-levels.manage']);
    }

    public function update(User $user, JobLevel $jobLevel): bool
    {
        return $user->hasAnyPermission(['job-levels.update', 'job-levels.manage']);
    }

    public function delete(User $user, JobLevel $jobLevel): bool
    {
        return $user->hasAnyPermission(['job-levels.delete', 'job-levels.manage']);
    }

    public function restore(User $user, JobLevel $jobLevel): bool
    {
        return $user->hasAnyPermission(['job-levels.restore', 'job-levels.manage']);
    }

    public function forceDelete(User $user, JobLevel $jobLevel): bool
    {
        return $user->hasAnyPermission(['job-levels.force-delete', 'job-levels.manage']);
    }
}
