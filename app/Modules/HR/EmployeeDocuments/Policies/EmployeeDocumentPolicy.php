<?php

namespace App\Modules\HR\EmployeeDocuments\Policies;

use App\Models\User;

class EmployeeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['employee-documents.view', 'employee-documents.manage']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['employee-documents.create', 'employee-documents.manage']);
    }
}
