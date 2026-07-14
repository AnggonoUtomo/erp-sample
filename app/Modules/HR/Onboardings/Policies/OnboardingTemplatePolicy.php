<?php

namespace App\Modules\HR\Onboardings\Policies;

use App\Models\User;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;

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

    public function delete(User $user, OnboardingTemplate $template): bool
    {
        return $user->hasAnyPermission(['onboardings.template-manage', 'onboardings.manage']);
    }

    public function restore(User $user, OnboardingTemplate $template): bool
    {
        return $user->hasAnyPermission(['onboardings.template-manage', 'onboardings.manage']);
    }
}
