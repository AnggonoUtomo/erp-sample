import type { AccessControlAbilities, PermissionFormData, PermissionGroup, RoleOption } from '@/pages/console/access-control/types';
import { useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { DeleteRoleDialog } from './delete-role-dialog';
import { PermissionModulePanel } from './permission-module-panel';
import { RoleControlCard } from './role-control-card';

interface RolePermissionWorkspaceProps {
    roles: RoleOption[];
    activeRole: RoleOption;
    activeRoleId: number | null;
    permissionGroups: PermissionGroup[];
    abilities: AccessControlAbilities;
    onRoleChange: (roleId: number | null) => void;
    onAddRole: () => void;
}

export function RolePermissionWorkspace({
    roles,
    activeRole,
    activeRoleId,
    permissionGroups,
    abilities,
    onRoleChange,
    onAddRole,
}: RolePermissionWorkspaceProps) {
    const [deleteRoleOpen, setDeleteRoleOpen] = useState(false);

    const visiblePermissionGroups = useMemo(() => {
        return permissionGroups
            .map((group) => ({
                ...group,
                permissions: group.permissions.filter((permission) => permission.guard_name === activeRole.guard_name),
            }))
            .filter((group) => group.permissions.length > 0);
    }, [permissionGroups, activeRole.guard_name]);

    const [openedModule, setOpenedModule] = useState<string | null>(visiblePermissionGroups[0]?.module ?? null);

    const { data, setData, put, processing, errors } = useForm<PermissionFormData>({
        permissions: activeRole.permissions ?? [],
    });

    const totalPermissionCount = visiblePermissionGroups.reduce((total, group) => total + group.permissions.length, 0);
    const selectedPermissionCount = data.permissions.length;
    const progress = totalPermissionCount > 0 ? Math.round((selectedPermissionCount / totalPermissionCount) * 100) : 0;
    const canUpdateActiveRole = abilities.canUpdateRole && !activeRole.is_protected;

    const isPermissionChecked = (permissionName: string) => data.permissions.includes(permissionName);

    const togglePermission = (permissionName: string) => {
        if (!canUpdateActiveRole) {
            return;
        }

        setData(
            'permissions',
            isPermissionChecked(permissionName) ? data.permissions.filter((name) => name !== permissionName) : [...data.permissions, permissionName],
        );
    };

    const isGroupChecked = (group: PermissionGroup) => {
        return group.permissions.length > 0 && group.permissions.every((permission) => data.permissions.includes(permission.name));
    };

    const toggleGroup = (group: PermissionGroup) => {
        if (!canUpdateActiveRole) {
            return;
        }

        const groupPermissionNames = group.permissions.map((permission) => permission.name);

        setData(
            'permissions',
            isGroupChecked(group)
                ? data.permissions.filter((permissionName) => !groupPermissionNames.includes(permissionName))
                : Array.from(new Set([...data.permissions, ...groupPermissionNames])),
        );
    };

    const getSelectedCountInGroup = (group: PermissionGroup) => {
        return group.permissions.filter((permission) => data.permissions.includes(permission.name)).length;
    };

    const resetToRolePermissions = () => {
        setData('permissions', activeRole.permissions ?? []);
    };

    const submit = () => {
        if (!canUpdateActiveRole) {
            return;
        }

        put(route('access-control.roles.permissions.sync', activeRole.id), {
            preserveScroll: true,
        });
    };

    return (
        <>
            <div className="grid gap-4 xl:grid-cols-[280px_1fr]">
                <RoleControlCard
                    roles={roles}
                    activeRole={activeRole}
                    activeRoleId={activeRoleId}
                    totalPermissionCount={totalPermissionCount}
                    selectedPermissionCount={selectedPermissionCount}
                    progress={progress}
                    processing={processing}
                    permissionError={errors.permissions}
                    abilities={abilities}
                    onRoleChange={onRoleChange}
                    onAddRole={onAddRole}
                    onReset={resetToRolePermissions}
                    onSubmit={submit}
                    onDeleteRole={() => setDeleteRoleOpen(true)}
                />

                <PermissionModulePanel
                    activeRole={activeRole}
                    groups={visiblePermissionGroups}
                    openedModule={openedModule}
                    canUpdateRole={canUpdateActiveRole}
                    onToggleModule={(module) => setOpenedModule((current) => (current === module ? null : module))}
                    isGroupChecked={isGroupChecked}
                    getSelectedCountInGroup={getSelectedCountInGroup}
                    isPermissionChecked={isPermissionChecked}
                    onToggleGroup={toggleGroup}
                    onTogglePermission={togglePermission}
                />
            </div>

            <DeleteRoleDialog open={deleteRoleOpen} onOpenChange={setDeleteRoleOpen} role={activeRole} />
        </>
    );
}
