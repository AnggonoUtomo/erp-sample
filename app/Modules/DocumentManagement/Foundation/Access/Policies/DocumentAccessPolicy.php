<?php

namespace App\Modules\DocumentManagement\Foundation\Access\Policies;

use App\Models\User;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessRequestV1;

class DocumentAccessPolicy
{
    private const ACTION_PERMISSIONS = [
        'VIEW' => 'documents.view',
        'DOWNLOAD' => 'documents.download',
    ];

    public function resolveActor(DocumentAccessRequestV1 $request): ?User
    {
        if ($request->actorReference === null
            || preg_match('/^user:([1-9][0-9]*)$/', $request->actorReference, $matches) !== 1) {
            return null;
        }

        return User::query()->find((int) $matches[1]);
    }

    public function allows(User $actor, string $action): bool
    {
        $permission = self::ACTION_PERMISSIONS[$action] ?? null;

        return $permission !== null && $actor->can($permission);
    }
}
