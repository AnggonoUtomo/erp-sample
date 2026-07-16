<?php

namespace App\Modules\HR\Offboardings\Policies;

use App\Models\User;

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
}
