<?php

namespace App\Modules\HR\HRReferenceData\Policies;

use App\Models\User;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;

class ReferenceDataPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['hr.view', 'hr-reference-data.view', 'hr-reference-data.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['hr-reference-data.create', 'hr-reference-data.manage']);
    }

    public function update(User $user, ReferenceData $referenceData): bool
    {
        return $user->hasAnyPermission(['hr-reference-data.update', 'hr-reference-data.manage']);
    }

    public function delete(User $user, ReferenceData $referenceData): bool
    {
        return $user->hasAnyPermission(['hr-reference-data.delete', 'hr-reference-data.manage']);
    }

    public function restore(User $user, ReferenceData $referenceData): bool
    {
        return $user->hasAnyPermission(['hr-reference-data.restore', 'hr-reference-data.manage']);
    }

    public function forceDelete(User $user, ReferenceData $referenceData): bool
    {
        return $user->hasAnyPermission(['hr-reference-data.force-delete', 'hr-reference-data.manage']);
    }
}
