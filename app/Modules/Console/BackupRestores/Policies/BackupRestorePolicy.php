<?php

namespace App\Modules\Console\BackupRestores\Policies;

use App\Models\User;

class BackupRestorePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('backup-restore.view');
    }

    public function export(User $user): bool
    {
        return $user->can('backup-restore.export');
    }

    public function restore(User $user): bool
    {
        return $user->can('backup-restore.restore');
    }

    public function fullExport(User $user): bool
    {
        return $user->can('backup-restore.full-export');
    }

    public function fullRestore(User $user): bool
    {
        return $user->can('backup-restore.full-restore');
    }
}
