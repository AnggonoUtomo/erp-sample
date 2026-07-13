<?php

namespace App\Modules\HR\EmployeeDocuments\Policies;

use App\Models\User;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;

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

    public function verify(User $user, EmployeeDocument $document): bool
    {
        return $user->hasAnyPermission(['employee-documents.verify', 'employee-documents.manage']);
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->hasAnyPermission(['employee-documents.archive', 'employee-documents.manage']);
    }

    public function restore(User $user, EmployeeDocument $document): bool
    {
        return $user->hasAnyPermission(['employee-documents.restore', 'employee-documents.manage']);
    }
}
