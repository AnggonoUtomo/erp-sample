<?php

namespace App\Modules\HR\Onboardings\Policies;

use App\Models\User;

class OnboardingTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['onboardings.view', 'onboardings.template-manage', 'onboardings.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['onboardings.template-manage', 'onboardings.manage']);
    }
}
