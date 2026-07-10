import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { UserRow } from '@/pages/console/users/types';
import { router } from '@inertiajs/react';
import { LogIn, ShieldAlert } from 'lucide-react';

type Props = {
    open: boolean;
    user: UserRow | null;
    onOpenChange: (open: boolean) => void;
};

export function ImpersonateUserDialog({ open, user, onOpenChange }: Props) {
    const startImpersonating = () => {
        if (!user) {
            return;
        }

        router.post(
            route('users.impersonate', user.id),
            {},
            {
                preserveScroll: true,
                onSuccess: () => onOpenChange(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <div className="mb-2 flex size-11 items-center justify-center rounded-lg bg-amber-500/15 text-amber-600 dark:text-amber-300">
                        <ShieldAlert className="size-5" />
                    </div>
                    <DialogTitle>Masuk sebagai user ini?</DialogTitle>
                    <DialogDescription className="leading-relaxed">
                        Kamu akan berpindah sementara dari akun admin ke akun {user?.name ?? 'user ini'}. Semua aktivitas start dan stop impersonate
                        akan tercatat di audit log.
                    </DialogDescription>
                </DialogHeader>

                {user ? (
                    <div className="bg-muted/50 rounded-lg border p-4 text-sm">
                        <p className="font-medium">{user.name}</p>
                        <p className="text-muted-foreground mt-1">{user.email}</p>
                    </div>
                ) : null}

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Batal
                    </Button>
                    <Button type="button" onClick={startImpersonating}>
                        <LogIn className="size-4" />
                        Login-as
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
