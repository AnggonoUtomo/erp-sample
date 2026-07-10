<?php

namespace App\Modules\Console\LoginActivities\Policies;

use App\Models\User;

class LoginActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('login-activities.view');
    }
}
