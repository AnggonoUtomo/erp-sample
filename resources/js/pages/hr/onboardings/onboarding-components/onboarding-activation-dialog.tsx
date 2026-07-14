import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { Play } from 'lucide-react';
import { useState } from 'react';

export function canActivateOnboarding(status: string, archived: boolean, hasPermission: boolean): boolean {
    return hasPermission && status === 'DRAFT' && !archived;
}

export function OnboardingActivationDialog({ onboardingId }: { onboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const activate = () => {
        router.patch(
            route('hr.onboardings.activate', onboardingId),
            {},
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => setOpen(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button>
                    <Play className="size-4" /> Aktifkan onboarding
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Aktifkan onboarding?</DialogTitle>
                    <DialogDescription>
                        Status akan berubah dari DRAFT menjadi IN_PROGRESS. Checklist snapshot tetap sama dan mulai dipantau sebagai proses aktif.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={activate} disabled={processing}>
                        {processing ? 'Mengaktifkan…' : 'Ya, aktifkan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
