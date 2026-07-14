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
}
