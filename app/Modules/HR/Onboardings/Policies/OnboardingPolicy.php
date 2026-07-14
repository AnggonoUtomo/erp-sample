<?php

namespace App\Modules\HR\Onboardings\Policies;

use App\Models\User;

class OnboardingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['onboardings.view', 'onboardings.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['onboardings.create', 'onboardings.manage']);
    }

    public function view(User $user, mixed $onboarding): bool
    {
        return $this->viewAny($user);
    }

    public function activate(User $user, mixed $onboarding): bool
    {
        return $user->hasAnyPermission(['onboardings.activate', 'onboardings.manage']);
    }

    public function complete(User $user, mixed $onboarding): bool
    {
        return $user->hasAnyPermission(['onboardings.complete', 'onboardings.manage']);
    }

    public function cancel(User $user, mixed $onboarding): bool
    {
        return $user->hasAnyPermission(['onboardings.cancel', 'onboardings.manage']);
    }

    public function delete(User $user, mixed $onboarding): bool
    {
        return $user->hasAnyPermission(['onboardings.archive', 'onboardings.manage']);
    }

    public function restore(User $user, mixed $onboarding): bool
    {
        return $user->hasAnyPermission(['onboardings.restore', 'onboardings.manage']);
    }
}
