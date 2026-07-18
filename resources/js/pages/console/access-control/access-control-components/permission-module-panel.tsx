import { Card, CardContent } from '@/components/ui/card';
import type { PermissionGroup, RoleOption } from '@/pages/console/access-control/types';
import { ChevronDown, ChevronRight, ShieldCheck } from 'lucide-react';

interface PermissionModulePanelProps {
    activeRole: RoleOption;
    groups: PermissionGroup[];
    openedModule: string | null;
    canUpdateRole: boolean;
    onToggleModule: (module: string) => void;
    isGroupChecked: (group: PermissionGroup) => boolean;
    getSelectedCountInGroup: (group: PermissionGroup) => number;
    isPermissionChecked: (permissionName: string) => boolean;
    onToggleGroup: (group: PermissionGroup) => void;
    onTogglePermission: (permissionName: string) => void;
}

export function PermissionModulePanel({
    activeRole,
    groups,
    openedModule,
    canUpdateRole,
    onToggleModule,
    isGroupChecked,
    getSelectedCountInGroup,
    isPermissionChecked,
    onToggleGroup,
    onTogglePermission,
}: PermissionModulePanelProps) {
    return (
        <Card data-dashboard-card className="min-h-[640px] overflow-hidden xl:max-h-[calc(100vh-96px)]">
            <CardContent className="flex h-full flex-col p-5">
                <div className="flex items-center justify-between gap-3">
                    <div className="flex min-w-0 items-center gap-3">
                        <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600">
                            <ShieldCheck className="size-4" />
                        </span>
                        <div className="min-w-0">
                            <h2 className="text-sm font-semibold">Permission Module</h2>
                            <p className="text-muted-foreground mt-1 text-xs">Klik module untuk membuka daftar permission.</p>
                        </div>
                    </div>
                    <div className="bg-muted text-muted-foreground rounded-full px-3 py-1 text-[11px] font-medium">{activeRole.name}</div>
                </div>

                {!canUpdateRole ? (
                    <div className="text-muted-foreground mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-xs">
                        Kamu hanya dapat melihat permission. Untuk mengubah permission, diperlukan akses{' '}
                        <span className="font-semibold">access-control.update</span>.
                    </div>
                ) : null}

                <div className="mt-3 min-h-0 flex-1 space-y-2 overflow-y-auto pr-1">
                    {groups.map((group, groupIndex) => {
                        const isOpen = openedModule === group.module;
                        const groupChecked = isGroupChecked(group);
                        const selectedInGroup = getSelectedCountInGroup(group);

                        return (
                            <div key={group.module} className="bg-background overflow-hidden rounded-lg border">
                                <div className="hover:bg-muted/40 flex items-center gap-3 px-3 py-2.5 transition">
                                    <input
                                        id={groupIndex === 0 ? 'access-permission-first' : undefined}
                                        type="checkbox"
                                        checked={groupChecked}
                                        disabled={!canUpdateRole}
                                        onChange={() => onToggleGroup(group)}
                                        className="border-muted-foreground size-4 shrink-0 rounded disabled:cursor-not-allowed disabled:opacity-50"
                                    />

                                    <button
                                        type="button"
                                        onClick={() => onToggleModule(group.module)}
                                        className="flex min-w-0 flex-1 items-center justify-between gap-3 text-left"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate text-sm leading-tight font-semibold">{group.label}</p>
                                            <p className="text-muted-foreground mt-0.5 text-[11px]">{group.permissions.length} permission tersedia</p>
                                        </div>

                                        <div className="flex shrink-0 items-center gap-2">
                                            <span className="rounded-full bg-sky-500/10 px-2.5 py-1 text-[11px] font-medium text-sky-700">
                                                {selectedInGroup}/{group.permissions.length}
                                            </span>
                                            {isOpen ? (
                                                <ChevronDown className="text-muted-foreground size-4" />
                                            ) : (
                                                <ChevronRight className="text-muted-foreground size-4" />
                                            )}
                                        </div>
                                    </button>
                                </div>

                                {isOpen ? (
                                    <div className="bg-muted/20 grid gap-2 border-t p-3 md:grid-cols-2 xl:grid-cols-3">
                                        {group.permissions.map((permission) => (
                                            <label
                                                key={permission.id}
                                                className="bg-background hover:border-primary/30 hover:bg-muted/40 flex cursor-pointer items-start gap-2.5 rounded-lg border p-2.5 transition"
                                            >
                                                <input
                                                    type="checkbox"
                                                    checked={isPermissionChecked(permission.name)}
                                                    disabled={!canUpdateRole}
                                                    onChange={() => onTogglePermission(permission.name)}
                                                    className="border-muted-foreground mt-0.5 size-4 rounded disabled:cursor-not-allowed disabled:opacity-50"
                                                />
                                                <div className="min-w-0">
                                                    <p className="text-xs leading-tight font-medium">{permission.label}</p>
                                                    <p className="text-muted-foreground mt-1 text-[11px] leading-tight break-all">
                                                        {permission.name}
                                                    </p>
                                                </div>
                                            </label>
                                        ))}
                                    </div>
                                ) : null}
                            </div>
                        );
                    })}

                    {groups.length === 0 ? (
                        <div className="rounded-lg border border-dashed p-6 text-center">
                            <p className="text-sm font-medium">Permission belum tersedia</p>
                            <p className="text-muted-foreground mt-1 text-xs">
                                Tambahkan permission terlebih dahulu melalui seeder atau endpoint permission.
                            </p>
                        </div>
                    ) : null}
                </div>
            </CardContent>
        </Card>
    );
}
