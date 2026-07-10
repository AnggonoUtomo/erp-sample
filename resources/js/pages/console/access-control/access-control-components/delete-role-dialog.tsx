import { Button } from '@/components/ui/button';
import type { RoleOption } from '@/pages/console/access-control/types';
import { useForm } from '@inertiajs/react';
import { AlertTriangle, Trash2, X } from 'lucide-react';

interface DeleteRoleDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    role: RoleOption;
}

export function DeleteRoleDialog({ open, onOpenChange, role }: DeleteRoleDialogProps) {
    const { delete: destroy, processing } = useForm<Record<string, never>>({});

    const submit = () => {
        destroy(route('access-control.roles.destroy', role.id), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => onOpenChange(false),
        });
    };

    if (!open) {
        return null;
    }

    return (
        <div className="bg-background/80 fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div className="bg-background w-full max-w-md rounded-lg border shadow-lg">
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <div className="flex items-center gap-3">
                        <div className="bg-destructive/10 text-destructive flex size-9 items-center justify-center rounded-lg">
                            <AlertTriangle className="size-5" />
                        </div>
                        <div>
                            <h2 className="text-sm font-semibold">Delete Role</h2>
                            <p className="text-muted-foreground mt-1 text-xs">Tindakan ini akan menghapus role dari sistem.</p>
                        </div>
                    </div>
                    <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onOpenChange(false)} disabled={processing}>
                        <X className="size-4" />
                    </Button>
                </div>

                <div className="space-y-4 p-4">
                    <div className="border-destructive/30 bg-destructive/5 rounded-lg border p-3">
                        <p className="text-destructive text-sm font-semibold">{role.name}</p>
                        <p className="text-muted-foreground mt-1 text-xs">Guard: {role.guard_name}</p>
                    </div>

                    <p className="text-muted-foreground text-xs">
                        Permission yang terkait dengan role ini akan dilepas, lalu role akan dihapus. Pastikan role ini memang sudah tidak digunakan.
                    </p>

                    <div className="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
                            Batal
                        </Button>
                        <Button type="button" variant="destructive" onClick={submit} disabled={processing}>
                            <Trash2 className="size-4" />
                            {processing ? 'Menghapus...' : 'Hapus Role'}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}
