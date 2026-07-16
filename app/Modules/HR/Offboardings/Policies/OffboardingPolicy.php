<?php

namespace App\Modules\HR\Offboardings\Policies;

use App\Models\User;

class OffboardingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['offboardings.view', 'offboardings.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['offboardings.create', 'offboardings.manage']);
    }

    public function view(User $user, mixed $offboarding): bool
    {
        return $this->viewAny($user);
    }

    public function activate(User $user, mixed $offboarding): bool
    {
        return $user->hasAnyPermission(['offboardings.activate', 'offboardings.manage']);
    }

    public function markReady(User $user, mixed $offboarding): bool
    {
        return $user->hasAnyPermission(['offboardings.mark-ready', 'offboardings.manage']);
    }

    public function cancel(User $user, mixed $offboarding): bool
    {
        return $user->hasAnyPermission(['offboardings.cancel', 'offboardings.manage']);
    }

    public function finalize(User $user, mixed $offboarding): bool
    {
        return $user->hasAnyPermission(['offboardings.finalize', 'offboardings.manage']);
    }
}
