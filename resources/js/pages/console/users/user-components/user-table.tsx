import { PaginationBar } from '@/components/pagination-bar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import type { RoleOption, UserRow } from '@/pages/console/users/types';
import { router } from '@inertiajs/react';
import { ArchiveRestore, Edit3, LogIn, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Meta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    data: UserRow[];
    meta: Meta;
    filters: {
        search?: string;
        role?: string;
        archive?: string;
        per_page?: number;
    };
    roles: RoleOption[];
    onAdd: () => void;
    onSelectUser: (user: UserRow) => void;
    onEditUser: (user: UserRow) => void;
    onDeleteUser: (user: UserRow) => void;
    onRestoreUser: (user: UserRow) => void;
    onImpersonateUser: (user: UserRow) => void;
}

const statusBadgeMap = {
    active: 'bg-emerald-600 text-white',
    inactive: 'bg-rose-600 text-white',
    suspended: 'bg-stone-600 text-white',
    archived: 'bg-amber-600 text-white',
} as const;

export default function UserDatatable({ data, meta, filters, roles, onAdd, onSelectUser, onEditUser, onDeleteUser, onRestoreUser, onImpersonateUser }: Props) {
    const { can } = usePermission();
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedRole, setSelectedRole] = useState(filters.role ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);
    const activeFilters = {
        search,
        role: selectedRole === 'all' ? undefined : selectedRole,
        archive,
        per_page: filters.per_page ?? meta.per_page,
    };

    useEffect(() => {
        if (!filterMounted.current) {
            filterMounted.current = true;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('users.index'),
                {
                    search,
                    role: selectedRole === 'all' ? undefined : selectedRole,
                    archive,
                    per_page: filters.per_page ?? meta.per_page,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 400);

        return () => clearTimeout(timeout);
    }, [archive, search, selectedRole, filters.per_page, meta.per_page]);

    return (
        <div className="w-full min-w-0 space-y-4">
            <div className="space-y-3">
                <div className="flex min-w-0 flex-1 flex-col gap-3 md:flex-row md:flex-wrap">
                    <div className="relative min-w-[220px] flex-1">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            id="user-search-input"
                            placeholder="Search user..."
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            className="h-11 w-full pl-9"
                        />
                    </div>

                    <Select value={selectedRole} onValueChange={setSelectedRole}>
                        <SelectTrigger id="user-role-filter-trigger" className="h-11 w-full md:w-[180px]">
                            <SelectValue placeholder="Filter role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Roles</SelectItem>
                            {roles.map((role) => (
                                <SelectItem key={role.id} value={role.name}>
                                    {role.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <Select value={archive} onValueChange={setArchive}>
                        <SelectTrigger id="user-archive-filter-trigger" className="h-11 w-full md:w-[170px]">
                            <SelectValue placeholder="Arsip" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Aktif saja</SelectItem>
                            <SelectItem value="with-trashed">Dengan arsip</SelectItem>
                            <SelectItem value="only-trashed">Arsip saja</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {can('users.create') && (
                    <Button onClick={onAdd} className="h-11 w-full gap-2 sm:w-auto">
                        <Plus className="size-4" />
                        Add User
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[620px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left">
                            <tr>
                                <th className="w-[42%] px-3 py-3 font-semibold">User</th>
                                <th className="w-[20%] px-3 py-3 font-semibold">Role</th>
                                <th className="w-[18%] px-3 py-3 font-semibold">Status</th>
                                <th className="w-[20%] px-3 py-3 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.length ? (
                                data.map((user, index) => (
                                    <tr key={user.id} className="hover:bg-muted/40 border-t transition">
                                        <td className="px-3 py-3">
                                            <button
                                                id={`user-table-row-${index}`}
                                                type="button"
                                                onClick={() => onSelectUser(user)}
                                                onKeyDown={(event) => {
                                                    if (event.key === 'Enter') {
                                                        event.preventDefault();
                                                        onSelectUser(user);
                                                    }
                                                }}
                                                className="focus-visible:ring-ring flex min-w-0 items-center gap-3 rounded-md text-left outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                            >
                                                <Avatar className="size-10 rounded-lg">
                                                    <AvatarImage src={user.avatar ?? undefined} alt={user.name} />
                                                    <AvatarFallback className="bg-primary/10 text-primary rounded-lg">{user.initials}</AvatarFallback>
                                                </Avatar>
                                                <span className="min-w-0">
                                                    <span className="block truncate font-medium">{user.name}</span>
                                                    <span className="text-muted-foreground block truncate">{user.email}</span>
                                                </span>
                                            </button>
                                        </td>
                                        <td className="px-3 py-3">
                                            {user.roles.length ? (
                                                <div className="flex gap-1">
                                                    <Badge className="rounded-sm px-1.5 capitalize">{user.primaryRole ?? user.roles[0]}</Badge>
                                                    {user.roles.length > 1 && (
                                                        <Badge variant="outline" className="px-1.5">
                                                            +{user.roles.length - 1}
                                                        </Badge>
                                                    )}
                                                </div>
                                            ) : (
                                                <span className="text-muted-foreground">No role</span>
                                            )}
                                        </td>
                                        <td className="px-3 py-3">
                                            <Badge className={`capitalize ${statusBadgeMap[user.status]}`}>{user.status}</Badge>
                                        </td>
                                        <td className="px-3 py-3">
                                            <div className="flex justify-end gap-1">
                                                {can('users.impersonate') && user.can?.impersonate && !user.deleted_at && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8"
                                                        title={`Impersonate ${user.name}`}
                                                        onClick={() => onImpersonateUser(user)}
                                                    >
                                                        <LogIn className="size-4" />
                                                    </Button>
                                                )}
                                                {can('users.update') && !user.deleted_at && (
                                                    <Button variant="ghost" size="icon" className="size-8" onClick={() => onEditUser(user)}>
                                                        <Edit3 className="size-4" />
                                                    </Button>
                                                )}
                                                {user.deleted_at && can('users.restore') && user.can?.restore !== false && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8"
                                                        title={`Pulihkan ${user.name}`}
                                                        onClick={() => onRestoreUser(user)}
                                                    >
                                                        <RotateCcw className="size-4" />
                                                    </Button>
                                                )}
                                                {!user.deleted_at && can('users.delete') && user.can?.delete !== false && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8 text-destructive hover:text-destructive"
                                                        title={`Arsipkan ${user.name}`}
                                                        onClick={() => onDeleteUser(user)}
                                                    >
                                                        <ArchiveRestore className="size-4" />
                                                    </Button>
                                                )}
                                                {user.deleted_at && can('users.force-delete') && user.can?.forceDelete !== false && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        className="size-8 text-destructive hover:text-destructive"
                                                        title={`Hapus permanen ${user.name}`}
                                                        onClick={() => onDeleteUser(user)}
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={4} className="text-muted-foreground h-24 px-3 py-6 text-center">
                                        No users found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-muted-foreground text-sm">
                    Showing {data.length} of {meta.total} users
                </div>
                <PaginationBar meta={meta} routeName="users.index" filters={activeFilters} idPrefix="user" />
            </div>
        </div>
    );
}
