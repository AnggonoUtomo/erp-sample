import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { UserRow } from '@/pages/console/users/types';
import { useForm } from '@inertiajs/react';
import { AlertTriangle, Trash2 } from 'lucide-react';

type Props = {
    open: boolean;
    user: UserRow | null;
    permanent?: boolean;
    onOpenChange: (open: boolean) => void;
    onDeleted?: () => void;
};

export function DeleteUserDialog({ open, user, permanent = false, onOpenChange, onDeleted }: Props) {
    const { delete: destroy, processing } = useForm<Record<string, never>>({});

    const submit = () => {
        if (!user) {
            return;
        }

        destroy(route(permanent ? 'users.force-destroy' : 'users.destroy', user.id), {
            preserveScroll: true,
            onSuccess: () => {
                onDeleted?.();
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={(nextOpen) => !processing && onOpenChange(nextOpen)}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <div className="flex items-center gap-3">
                        <div className="bg-destructive/10 text-destructive flex size-10 items-center justify-center rounded-lg">
                            <AlertTriangle className="size-5" />
                        </div>
                        <div>
                            <DialogTitle>{permanent ? 'Hapus Permanen User' : 'Arsipkan User'}</DialogTitle>
                            <DialogDescription className="mt-1">
                                {permanent
                                    ? 'Tindakan ini akan menghapus permanen akun user dari sistem.'
                                    : 'Tindakan ini akan memindahkan user ke arsip dan masih bisa dipulihkan.'}
                            </DialogDescription>
                        </div>
                    </div>
                </DialogHeader>

                {user && (
                    <div className="border-destructive/30 bg-destructive/5 flex items-center gap-3 rounded-lg border p-3">
                        <Avatar className="size-12 rounded-lg">
                            <AvatarImage src={user.avatar ?? undefined} alt={user.name} />
                            <AvatarFallback className="bg-background text-destructive rounded-lg">{user.initials}</AvatarFallback>
                        </Avatar>
                        <div className="min-w-0">
                            <p className="text-destructive truncate text-sm font-semibold">{user.name}</p>
                            <p className="text-muted-foreground truncate text-xs">{user.email}</p>
                        </div>
                    </div>
                )}

                <p className="text-muted-foreground text-sm">
                    {permanent
                        ? 'Pastikan data user ini memang tidak dibutuhkan lagi. Operasi hapus permanen tidak bisa dibatalkan.'
                        : 'User yang diarsipkan tidak muncul pada daftar aktif, tidak bisa diedit, dan dapat dipulihkan dari filter arsip.'}
                </p>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" onClick={submit} disabled={processing || !user}>
                        <Trash2 className="size-4" />
                        {processing ? 'Memproses...' : permanent ? 'Hapus Permanen' : 'Arsipkan User'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
