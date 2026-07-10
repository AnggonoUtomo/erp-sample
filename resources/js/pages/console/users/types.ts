export type UserRow = {
    id: number;
    name: string;
    email: string;
    initials: string;
    roles: string[];
    permissions: string[];
    effectivePermissions: string[];
    primaryRole?: string;
    status: 'active' | 'inactive' | 'suspended' | 'archived';
    lastLogin?: string;
    created_at?: string | null;
    deleted_at?: string | null;
    avatar?: string | null;
    rolePermissions: Record<string, string[]>;
    can?: {
        update: boolean;
        delete: boolean;
        restore: boolean;
        forceDelete: boolean;
        impersonate: boolean;
    };
};

export type RoleOption = {
    id: number;
    name: string;
    permissions: string[];
};

export type PermissionOption = {
    id: number;
    name: string;
    guard_name: string;
};

export type PermissionGroup = {
    module: string;
    permissions: PermissionOption[];
};

export type PaginatedUsers = {
    data: UserRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
