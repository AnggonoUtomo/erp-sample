import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { PermissionGroup, RoleOption, UserRow } from '@/pages/console/users/types';
import { Pencil, UserPlus } from 'lucide-react';
import UserDetailCard from './user-detail-card';
import UserForm from './user-form';

export type UserWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: UserWorkspaceMode;
    user: UserRow | null;
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
    onCancel: () => void;
    onSuccess: () => void;
};

export default function UserWorkspaceCard({ mode, user, roles, permissionGroups, onCancel, onSuccess }: Props) {
    if (mode === 'detail') {
        return <UserDetailCard user={user} roles={roles} permissionGroups={permissionGroups} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : UserPlus;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-indigo flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit User' : 'Add User'}
                        </CardTitle>
                        <p className="text-muted-foreground text-sm">
                            {isEdit
                                ? 'Perbarui identitas, avatar, role, dan kirim tautan atur password bila diperlukan.'
                                : 'Buat akun baru dan tentukan role awalnya.'}
                        </p>
                    </div>
                </div>
            </CardHeader>

            <CardContent className="p-5">
                <UserForm
                    key={isEdit ? `edit-${user?.id ?? 'empty'}` : 'create'}
                    user={isEdit ? user : null}
                    roles={roles}
                    permissionGroups={permissionGroups}
                    onSuccess={onSuccess}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
