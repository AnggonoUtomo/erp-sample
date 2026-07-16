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

export function canActivateOffboarding(status: string, archived: boolean, hasPermission: boolean): boolean {
    return hasPermission && status === 'DRAFT' && !archived;
}

export function OffboardingActivationDialog({ offboardingId }: { offboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const activate = () => {
        router.patch(
            route('hr.offboardings.activate', offboardingId),
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
                    <Play className="size-4" /> Aktifkan offboarding
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Aktifkan offboarding?</DialogTitle>
                    <DialogDescription>
                        Status akan berubah dari DRAFT menjadi IN_PROGRESS. Konteks exit dan checklist snapshot tetap sama, tanpa mengubah profile
                        Employee maupun Contract.
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
