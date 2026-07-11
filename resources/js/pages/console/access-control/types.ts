import type { SharedData } from '@/types';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';

export interface RoleOption {
    id: number;
    name: string;
    guard_name: string;
    permissions: string[];
    is_protected: boolean;
}

export interface PermissionItem {
    id: number;
    name: string;
    guard_name: string;
    module: string;
    module_label: string;
    action: string;
    label: string;
}

export interface PermissionGroup {
    module: string;
    label: string;
    permissions: PermissionItem[];
}

export interface AccessControlPageProps extends InertiaPageProps, SharedData {
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
    selectedRoleId: number | null;
}

export interface AccessControlAbilities {
    canCreateRole: boolean;
    canUpdateRole: boolean;
    canDeleteRole: boolean;
}

export interface PermissionFormData {
    [key: string]: string[];
    permissions: string[];
}

export interface CreateRoleFormData {
    [key: string]: string;
    name: string;
    guard_name: string;
}
