<?php

namespace App\Modules\HR\Offboardings\Policies;

use App\Models\User;

class OffboardingTaskPolicy
{
    public function update(User $user, mixed $task = null): bool
    {
        return $user->hasAnyPermission(['offboardings.task-update', 'offboardings.manage']);
    }
}
