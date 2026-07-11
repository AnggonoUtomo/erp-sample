import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import type { PaginatedUsers, PermissionGroup, RoleOption, UserRow } from './types';
import { DeleteUserDialog } from './user-components/delete-user-dialog';
import { ImpersonateUserDialog } from './user-components/impersonate-user-dialog';
import { UserShortcutPanel } from './user-components/user-shortcut-panel';
import UserSummaryCards from './user-components/user-summary-cards';
import UserDatatable from './user-components/user-table';
import UserWorkspaceCard, { type UserWorkspaceMode } from './user-components/user-workspace-card';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'User',
        href: '/users',
    },
];

interface PageProps extends SharedData {
    users: PaginatedUsers;
    filters: {
        search?: string;
        role?: string;
        archive?: string;
        per_page?: number;
    };
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
}

export default function UserIndex() {
    const { users, filters, roles, permissionGroups } = usePage<PageProps>().props;
    const { can } = usePermission();
    const [selectedUser, setSelectedUser] = useState<UserRow | null>(null);
    const [editingUser, setEditingUser] = useState<UserRow | null>(null);
    const [deletingUser, setDeletingUser] = useState<UserRow | null>(null);
    const [impersonatingUser, setImpersonatingUser] = useState<UserRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<UserWorkspaceMode>('detail');

    const handleAdd = () => {
        setEditingUser(null);
        setWorkspaceMode('create');
    };

    const handleEdit = (user: UserRow) => {
        setSelectedUser(user);
        setEditingUser(user);
        setWorkspaceMode('edit');
    };

    const handleSelectUser = (user: UserRow) => {
        setSelectedUser(user);
        setEditingUser(null);
        setWorkspaceMode('detail');
    };

    const handleCancel = () => {
        setEditingUser(null);
        setWorkspaceMode('detail');
    };

    const handleFormSuccess = () => {
        setSelectedUser(null);
        setEditingUser(null);
        setWorkspaceMode('detail');
    };

    const handleDeleteUser = useCallback(
        (user: UserRow) => {
            if (user.deleted_at) {
                if (!can('users.force-delete') || user.can?.forceDelete === false) {
                    return;
                }

                setDeletingUser(user);
                return;
            }

            if (!can('users.delete') || user.can?.delete === false) {
                return;
            }

            setDeletingUser(user);
        },
        [can],
    );

    const handleRestoreUser = (user: UserRow) => {
        if (!can('users.restore') || user.can?.restore === false) {
            return;
        }

        router.patch(
            route('users.restore', user.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (selectedUser?.id === user.id) {
                        setSelectedUser(null);
                    }
                },
            },
        );
    };

    const handleImpersonateUser = (user: UserRow) => {
        if (!can('users.impersonate') || !user.can?.impersonate) {
            return;
        }

        setImpersonatingUser(user);
    };

    const handleDeletedUser = () => {
        setSelectedUser(null);
        setEditingUser(null);
        setWorkspaceMode('detail');
    };

    useEffect(() => {
        const isEditableTarget = (target: EventTarget | null) => {
            if (!(target instanceof HTMLElement)) {
                return false;
            }

            return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
        };

        const handleShortcut = (event: KeyboardEvent) => {
            const key = event.key.toLowerCase();

            if ((event.metaKey || event.ctrlKey) && event.shiftKey && key === 'a' && can('users.create')) {
                event.preventDefault();
                handleAdd();
                return;
            }

            if ((event.metaKey || event.ctrlKey) && key === 'k') {
                event.preventDefault();
                document.getElementById('user-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('user-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('user-role-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('user-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#user-pagination-next:not(:disabled), #user-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('user-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedUser) {
                event.preventDefault();
                handleDeleteUser(selectedUser);
                return;
            }

            if (event.key === 'Escape') {
                handleCancel();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [can, handleDeleteUser, selectedUser]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="User Management" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Manajemen User</h1>
                    <p className="text-muted-foreground mt-1 text-sm">Kelola akun, avatar, role, dan permission tambahan user.</p>
                </div>

                <UserSummaryCards users={users.data} total={users.total} roleCount={roles.length} />
                <UserShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <UserDatatable
                                data={users.data}
                                meta={{
                                    current_page: users.current_page,
                                    last_page: users.last_page,
                                    per_page: users.per_page,
                                    total: users.total,
                                }}
                                filters={filters}
                                roles={roles}
                                onAdd={handleAdd}
                                onSelectUser={handleSelectUser}
                                onEditUser={handleEdit}
                                onDeleteUser={handleDeleteUser}
                                onRestoreUser={handleRestoreUser}
                                onImpersonateUser={handleImpersonateUser}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <UserWorkspaceCard
                            mode={workspaceMode}
                            user={workspaceMode === 'edit' ? editingUser : selectedUser}
                            roles={roles}
                            permissionGroups={permissionGroups}
                            onCancel={handleCancel}
                            onSuccess={handleFormSuccess}
                        />
                    </div>
                </div>
            </div>

            <DeleteUserDialog
                open={Boolean(deletingUser)}
                user={deletingUser}
                permanent={Boolean(deletingUser?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeletingUser(null);
                    }
                }}
                onDeleted={handleDeletedUser}
            />

            <ImpersonateUserDialog
                open={Boolean(impersonatingUser)}
                user={impersonatingUser}
                onOpenChange={(open) => {
                    if (!open) {
                        setImpersonatingUser(null);
                    }
                }}
            />
        </AppLayout>
    );
}
