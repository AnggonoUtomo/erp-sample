<?php

namespace App\Modules\HR\Offboardings\Policies;

use App\Models\User;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;

class OffboardingTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'offboardings.view',
            'offboardings.template-manage',
            'offboardings.manage',
        ]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'offboardings.template-manage',
            'offboardings.manage',
        ]);
    }

    public function delete(User $user, OffboardingTemplate $template): bool
    {
        return $user->hasAnyPermission([
            'offboardings.template-manage',
            'offboardings.manage',
        ]);
    }

    public function restore(User $user, OffboardingTemplate $template): bool
    {
        return $user->hasAnyPermission([
            'offboardings.template-manage',
            'offboardings.manage',
        ]);
    }
}
