import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { LogOut, UserRoundCheck } from 'lucide-react';

export function ImpersonationBanner() {
    const { auth } = usePage<SharedData>().props;

    if (!auth.impersonation.active || !auth.impersonation.impersonator || !auth.user) {
        return null;
    }

    const stopImpersonating = () => {
        router.post(
            route('users.impersonate.stop'),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <div className="border-b border-amber-300/70 bg-amber-50 px-4 py-3 text-amber-950 sm:px-6 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            <div className="mx-auto flex max-w-7xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex min-w-0 items-start gap-3">
                    <span className="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-md bg-amber-500/15">
                        <UserRoundCheck className="size-5" />
                    </span>
                    <div className="min-w-0">
                        <p className="text-sm font-semibold">Sedang impersonate sebagai {auth.user.name}</p>
                        <p className="mt-0.5 text-xs opacity-80">
                            Admin asli: {auth.impersonation.impersonator.name} ({auth.impersonation.impersonator.email})
                        </p>
                    </div>
                </div>
                <Button type="button" size="sm" variant="outline" onClick={stopImpersonating} className="bg-background/80">
                    <LogOut className="size-4" />
                    Kembali ke Admin
                </Button>
            </div>
        </div>
    );
}
