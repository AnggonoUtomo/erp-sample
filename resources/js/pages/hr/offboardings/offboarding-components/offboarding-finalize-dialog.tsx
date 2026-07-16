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
import { UserRoundCheck } from 'lucide-react';
import { useState } from 'react';

export function canFinalizeOffboarding(status: string, archived: boolean, businessDate: string, exitDate: string, hasPermission: boolean): boolean {
    return hasPermission && !archived && status === 'READY_FOR_EXIT' && businessDate >= exitDate;
}

export function OffboardingFinalizeDialog({
    offboardingId,
    businessDate,
    exitDate,
}: {
    offboardingId: number;
    businessDate: string;
    exitDate: string;
}) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const finalize = () => {
        router.patch(
            route('hr.offboardings.finalize', offboardingId),
            { business_date: businessDate },
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
                <Button variant="destructive">
                    <UserRoundCheck className="size-4" /> Finalisasi exit
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Finalisasi employment exit?</DialogTitle>
                    <DialogDescription>
                        Employee akan dinonaktifkan dan contract aktif akan diakhiri pada {exitDate}. Proses memakai business date {businessDate} dan
                        dijalankan secara atomik.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="button" variant="destructive" onClick={finalize} disabled={processing}>
                        {processing ? 'Memfinalisasi…' : 'Ya, finalisasi exit'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
