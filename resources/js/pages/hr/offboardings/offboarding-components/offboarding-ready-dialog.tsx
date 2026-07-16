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
import { BadgeCheck } from 'lucide-react';
import { useState } from 'react';

export function canMarkOffboardingReady(status: string, archived: boolean, requiredIncomplete: number, hasPermission: boolean): boolean {
    return hasPermission && !archived && status === 'IN_PROGRESS' && requiredIncomplete === 0;
}

export function OffboardingReadyDialog({ offboardingId }: { offboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const markReady = () => {
        router.patch(
            route('hr.offboardings.mark-ready', offboardingId),
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
                    <BadgeCheck className="size-4" /> Tandai siap exit
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tandai offboarding siap exit?</DialogTitle>
                    <DialogDescription>
                        Status berubah menjadi READY_FOR_EXIT. Langkah ini hanya menandai kesiapan proses dan belum menonaktifkan Employee atau
                        mengakhiri Contract.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={markReady} disabled={processing}>
                        {processing ? 'Menyimpan…' : 'Ya, tandai siap'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
