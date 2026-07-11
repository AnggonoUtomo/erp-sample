import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import type { PermissionGroup, RoleOption, UserRow } from '@/pages/console/users/types';
import { CheckCircle2, Mail, ShieldCheck, UserRound } from 'lucide-react';

const roleColorMap: Record<string, string> = {
    'super-admin': 'bg-purple-600 text-white',
    'admin-ops': 'bg-sky-600 text-white',
    'finance-admin': 'bg-emerald-600 text-white',
    'customer-support': 'bg-amber-500 text-white',
    'provider-owner': 'bg-indigo-600 text-white',
    technician: 'bg-cyan-600 text-white',
    customer: 'bg-rose-600 text-white',
};

const statusBadgeMap = {
    active: 'bg-green-600 text-white',
    inactive: 'bg-red-600 text-white',
    suspended: 'bg-stone-600 text-white',
    archived: 'bg-amber-600 text-white',
} as const;

export default function UserDetailCard({
    user,
    roles,
    permissionGroups,
}: {
    user: UserRow | null;
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
}) {
    if (!user) {
        return (
            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="bg-muted/20 border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-indigo flex size-8 items-center justify-center rounded-lg">
                            <UserRound className="size-4" />
                        </span>
                        User Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="flex min-h-[420px] flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                    <div className="dashboard-icon icon-tone-fuchsia flex size-16 items-center justify-center rounded-lg">
                        <UserRound className="size-8" />
                    </div>
                    <div className="space-y-1">
                        <p className="font-medium">Pilih user dari tabel</p>
                        <p className="text-muted-foreground max-w-[260px] text-sm">Detail profil, role, dan permission akan tampil di area ini.</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    const roleNames = user.roles ?? [];
    const directPermissions = user.permissions ?? [];
    const rolePermissionCount = Object.values(user.rolePermissions ?? {}).reduce((total, permissions) => total + permissions.length, 0);
    const effectivePermissionCount = user.effectivePermissions.length ?? 0;
    const knownRoles = roles.map((role) => role.name);
    const hiddenRoleCount = roleNames.filter((role) => !knownRoles.includes(role)).length;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="bg-muted/20 border-b">
                <CardTitle className="flex items-center gap-2 text-base">
                    <span className="dashboard-icon icon-tone-indigo flex size-8 items-center justify-center rounded-lg">
                        <UserRound className="size-4" />
                    </span>
                    User Preview
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5 p-5">
                <div className="flex flex-col items-center text-center">
                    <Avatar className="ring-background size-28 rounded-lg ring-4">
                        {user.avatar ? <AvatarImage src={user.avatar} /> : null}
                        <AvatarFallback className="rounded-lg text-2xl">{user.initials}</AvatarFallback>
                    </Avatar>

                    <div className="mt-4 min-w-0 space-y-1">
                        <p className="truncate text-xl font-semibold">{user.name}</p>
                        <div className="text-muted-foreground flex items-center justify-center gap-1.5 text-sm">
                            <Mail className="size-3.5" />
                            <span className="truncate">{user.email}</span>
                        </div>
                    </div>

                    <Badge className={`mt-3 capitalize ${statusBadgeMap[user.status]}`}>{user.status}</Badge>
                </div>

                <Separator />

                <div className="grid gap-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-muted-foreground">Last Login</span>
                        <span className="text-right font-medium">{user.lastLogin ?? '-'}</span>
                    </div>
                    {user.deleted_at && (
                        <div className="flex items-center justify-between gap-3">
                            <span className="text-muted-foreground">Diarsipkan</span>
                            <span className="text-right font-medium">{user.deleted_at}</span>
                        </div>
                    )}
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-muted-foreground">Permission Dari Role</span>
                        <Badge variant="outline">{rolePermissionCount} inherited</Badge>
                    </div>
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-muted-foreground">Direct Permission Tambahan</span>
                        <Badge variant="outline">{directPermissions.length} custom</Badge>
                    </div>
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-muted-foreground">Effective Permission</span>
                        <Badge variant="outline">{effectivePermissionCount} total</Badge>
                    </div>
                </div>

                <Separator />

                <div className="space-y-3">
                    <div className="flex items-center gap-2">
                        <ShieldCheck className="size-4 text-[color:var(--block-accent)]" />
                        <p className="text-sm font-medium">Assigned Roles</p>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        {roleNames.length ? (
                            roleNames.map((role) => (
                                <Badge key={role} className={`capitalize ${roleColorMap[role] ?? 'bg-muted text-muted-foreground'}`}>
                                    {role}
                                </Badge>
                            ))
                        ) : (
                            <p className="text-muted-foreground text-sm">Belum ada role.</p>
                        )}
                        {hiddenRoleCount > 0 && <Badge variant="outline">{hiddenRoleCount} external</Badge>}
                    </div>
                </div>

                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <p className="text-sm font-medium">Permission Dari Role</p>
                        <Badge variant="secondary">{roleNames.length} role</Badge>
                    </div>
                    <div className="h-[220px] overflow-y-auto rounded-lg border">
                        <div className="space-y-3 p-3">
                            {roleNames.length ? (
                                roleNames.map((role) => (
                                    <div key={role} className="bg-background/60 rounded-lg border p-3">
                                        <div className="mb-3 flex items-center justify-between gap-2">
                                            <Badge className={`capitalize ${roleColorMap[role] ?? 'bg-muted text-muted-foreground'}`}>{role}</Badge>
                                            <span className="text-muted-foreground text-xs">
                                                {user.rolePermissions?.[role]?.length ?? 0} permission
                                            </span>
                                        </div>
                                        <PermissionGroups permissions={user.rolePermissions?.[role] ?? []} permissionGroups={permissionGroups} />
                                    </div>
                                ))
                            ) : (
                                <p className="text-muted-foreground text-sm">User belum memiliki role, jadi belum ada inherited permission.</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <p className="text-sm font-medium">Direct Permission Tambahan</p>
                        <Badge variant="secondary">{directPermissions.length} custom</Badge>
                    </div>
                    <div className="max-h-[180px] overflow-y-auto rounded-lg border">
                        <div className="p-3">
                            {directPermissions.length ? (
                                <PermissionGroups permissions={directPermissions} permissionGroups={permissionGroups} />
                            ) : (
                                <p className="text-muted-foreground text-sm">
                                    Tidak ada direct permission tambahan. Akses user ini berasal dari role.
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function PermissionGroups({ permissions, permissionGroups }: { permissions: string[]; permissionGroups: PermissionGroup[] }) {
    const permissionSet = new Set(permissions);
    const knownPermissions = new Set(permissionGroups.flatMap((group) => group.permissions.map((permission) => permission.name)));
    const externalPermissions = permissions.filter((permission) => !knownPermissions.has(permission));

    return (
        <div className="space-y-3">
            {permissionGroups.map((group) => {
                const matchedPermissions = group.permissions.filter((permission) => permissionSet.has(permission.name));

                if (!matchedPermissions.length) {
                    return null;
                }

                return (
                    <div key={group.module} className="space-y-2">
                        <p className="text-muted-foreground text-xs font-semibold uppercase">{group.module}</p>
                        <div className="flex flex-wrap gap-1.5">
                            {matchedPermissions.map((permission) => (
                                <Badge key={permission.id} variant="outline" className="gap-1 text-xs">
                                    <CheckCircle2 className="size-3 text-emerald-500" />
                                    {permission.name}
                                </Badge>
                            ))}
                        </div>
                    </div>
                );
            })}
            {externalPermissions.length > 0 && (
                <div className="space-y-2">
                    <p className="text-muted-foreground text-xs font-semibold uppercase">external</p>
                    <div className="flex flex-wrap gap-1.5">
                        {externalPermissions.map((permission) => (
                            <Badge key={permission} variant="outline" className="gap-1 text-xs">
                                <CheckCircle2 className="size-3 text-emerald-500" />
                                {permission}
                            </Badge>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
