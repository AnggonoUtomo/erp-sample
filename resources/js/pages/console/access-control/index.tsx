import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { AccessControlHeader } from './access-control-components/access-control-header';
import { AccessControlShortcutPanel } from './access-control-components/access-control-shortcut-panel';
import { AddRoleDialog } from './access-control-components/add-role-dialog';
import { EmptyRoleWorkspace } from './access-control-components/empty-role-workspace';
import { RolePermissionWorkspace } from './access-control-components/role-permission-workspace';
import type { AccessControlPageProps } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Access Control',
        href: '/access-control',
    },
];

export default function AccessControlIndex() {
    const { roles, permissionGroups, selectedRoleId, auth } = usePage<AccessControlPageProps>().props;

    const canCreateRole = auth.super || Boolean(auth.permissions['roles.manage']) || Boolean(auth.permissions['access-control.create']);
    const canUpdateRole = auth.super || Boolean(auth.permissions['roles.manage']) || Boolean(auth.permissions['access-control.update']);
    const canDeleteRole = auth.super || Boolean(auth.permissions['roles.manage']) || Boolean(auth.permissions['access-control.delete']);

    const defaultRoleId = selectedRoleId ?? roles[0]?.id ?? null;
    const [activeRoleId, setActiveRoleId] = useState<number | null>(defaultRoleId);
    const [addRoleOpen, setAddRoleOpen] = useState(false);

    const activeRole = useMemo(() => roles.find((role) => role.id === activeRoleId) ?? null, [roles, activeRoleId]);

    useEffect(() => {
        const isEditableTarget = (target: EventTarget | null) => {
            if (!(target instanceof HTMLElement)) {
                return false;
            }

            return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
        };

        const handleShortcut = (event: KeyboardEvent) => {
            const key = event.key.toLowerCase();

            if ((event.metaKey || event.ctrlKey) && event.shiftKey && key === 'a' && canCreateRole) {
                event.preventDefault();
                setAddRoleOpen(true);
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('access-role-select')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                document.getElementById('access-permission-first')?.focus();
                return;
            }

            if ((event.metaKey || event.ctrlKey) && key === 's' && canUpdateRole) {
                event.preventDefault();
                document.getElementById('access-save-permissions')?.click();
                return;
            }

            if (event.key === 'Delete' && !isEditableTarget(event.target) && canDeleteRole && !activeRole?.is_protected) {
                event.preventDefault();
                document.getElementById('access-delete-role')?.click();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [activeRole?.is_protected, canCreateRole, canDeleteRole, canUpdateRole]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Access Control" />

            <AddRoleDialog open={addRoleOpen} onOpenChange={setAddRoleOpen} />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <AccessControlHeader />
                <AccessControlShortcutPanel />

                {activeRole ? (
                    <RolePermissionWorkspace
                        key={activeRole.id}
                        roles={roles}
                        activeRole={activeRole}
                        activeRoleId={activeRoleId}
                        permissionGroups={permissionGroups}
                        abilities={{
                            canCreateRole,
                            canUpdateRole,
                            canDeleteRole,
                        }}
                        onRoleChange={setActiveRoleId}
                        onAddRole={() => setAddRoleOpen(true)}
                    />
                ) : (
                    <EmptyRoleWorkspace canCreateRole={canCreateRole} onAddRole={() => setAddRoleOpen(true)} />
                )}
            </div>
        </AppLayout>
    );
}
