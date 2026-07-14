<?php

namespace App\Modules\HR\Onboardings\Policies;

use App\Models\User;

class OnboardingTaskPolicy
{
    public function update(User $user, mixed $task = null): bool
    {
        return $user->hasAnyPermission(['onboardings.task-update', 'onboardings.manage']);
    }
}
