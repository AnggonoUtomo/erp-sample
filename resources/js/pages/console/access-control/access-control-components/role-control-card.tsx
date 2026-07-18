import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { AccessControlAbilities, RoleOption } from '@/pages/console/access-control/types';
import { KeyRound, Plus, RefreshCcw, Save, Trash2 } from 'lucide-react';
import { SummaryBox } from './summary-box';

interface RoleControlCardProps {
    roles: RoleOption[];
    activeRole: RoleOption;
    activeRoleId: number | null;
    totalPermissionCount: number;
    selectedPermissionCount: number;
    progress: number;
    processing: boolean;
    permissionError?: string;
    abilities: AccessControlAbilities;
    onRoleChange: (roleId: number | null) => void;
    onAddRole: () => void;
    onReset: () => void;
    onSubmit: () => void;
    onDeleteRole: () => void;
}

export function RoleControlCard({
    roles,
    activeRole,
    activeRoleId,
    totalPermissionCount,
    selectedPermissionCount,
    progress,
    processing,
    permissionError,
    abilities,
    onRoleChange,
    onAddRole,
    onReset,
    onSubmit,
    onDeleteRole,
}: RoleControlCardProps) {
    const canDeleteActiveRole = abilities.canDeleteRole && !activeRole.is_protected;
    const canUpdateActiveRole = abilities.canUpdateRole && !activeRole.is_protected;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardContent className="space-y-4 p-5">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="flex size-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600">
                                <KeyRound className="size-4" />
                            </span>
                            <h2 className="text-sm font-semibold">Pilih Role</h2>
                        </div>
                        <p className="text-muted-foreground mt-1 text-xs">Role aktif menjadi target permission.</p>
                    </div>

                    {abilities.canCreateRole ? (
                        <Button type="button" size="icon" className="size-8" title="Tambah role" onClick={onAddRole}>
                            <Plus className="size-4" />
                        </Button>
                    ) : null}
                </div>

                <select
                    id="access-role-select"
                    value={activeRoleId ?? ''}
                    onChange={(event) => onRoleChange(event.target.value ? Number(event.target.value) : null)}
                    className="bg-background focus:border-primary focus:ring-primary/20 h-9 w-full rounded-lg border px-3 text-xs transition outline-none focus:ring-2"
                >
                    {roles.map((role) => (
                        <option key={role.id} value={role.id}>
                            {role.name}
                        </option>
                    ))}
                </select>

                <div className="bg-muted/30 rounded-lg border p-3">
                    <div className="flex items-center justify-between gap-2">
                        <p className="text-sm leading-tight font-semibold">{activeRole.name}</p>
                        {activeRole.is_protected ? (
                            <span className="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-[10px] font-semibold">Protected</span>
                        ) : null}
                    </div>
                    <p className="text-muted-foreground mt-1 text-xs">Guard: {activeRole.guard_name}</p>
                </div>

                <div className="grid grid-cols-2 gap-2">
                    <SummaryBox label="Total Role" value={roles.length} tone="bg-sky-500/10" />
                    <SummaryBox label="Permission" value={totalPermissionCount} tone="bg-violet-500/10" />
                    <SummaryBox label="Terpilih" value={selectedPermissionCount} tone="bg-emerald-500/10" />
                    <SummaryBox label="Progress" value={`${progress}%`} tone="bg-amber-500/10" />
                </div>

                <div>
                    <div className="text-muted-foreground mb-1.5 flex items-center justify-between text-[11px]">
                        <span>Permission aktif</span>
                        <span>
                            {selectedPermissionCount}/{totalPermissionCount}
                        </span>
                    </div>
                    <div className="bg-muted h-2 overflow-hidden rounded-full">
                        <div className="h-full rounded-full bg-emerald-500 transition-all" style={{ width: `${progress}%` }} />
                    </div>
                </div>

                {permissionError ? <p className="text-destructive text-xs">{permissionError}</p> : null}

                <div className="flex gap-2">
                    <Button type="button" variant="outline" className="flex-1" onClick={onReset} disabled={processing || !canUpdateActiveRole}>
                        <RefreshCcw className="size-4" />
                        Reset
                    </Button>
                    <Button
                        id="access-save-permissions"
                        type="button"
                        className="flex-1"
                        onClick={onSubmit}
                        disabled={processing || !canUpdateActiveRole}
                    >
                        <Save className="size-4" />
                        {processing ? 'Simpan...' : 'Simpan'}
                    </Button>
                </div>

                {canDeleteActiveRole ? (
                    <Button
                        id="access-delete-role"
                        type="button"
                        variant="outline"
                        className="border-destructive/40 text-destructive hover:bg-destructive/10 w-full"
                        onClick={onDeleteRole}
                        disabled={processing}
                    >
                        <Trash2 className="size-4" />
                        Delete Role
                    </Button>
                ) : null}
            </CardContent>
        </Card>
    );
}
