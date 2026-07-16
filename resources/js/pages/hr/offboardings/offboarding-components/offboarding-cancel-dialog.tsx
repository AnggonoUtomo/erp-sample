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
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { Ban } from 'lucide-react';
import { useState } from 'react';

export function canCancelOffboarding(status: string, archived: boolean, hasPermission: boolean): boolean {
    return hasPermission && !archived && ['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'].includes(status);
}

export function OffboardingCancelDialog({ offboardingId }: { offboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [processing, setProcessing] = useState(false);

    const cancel = () => {
        router.patch(
            route('hr.offboardings.cancel', offboardingId),
            { reason: reason.trim() },
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => {
                    setOpen(false);
                    setReason('');
                },
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="destructive">
                    <Ban className="size-4" /> Batalkan offboarding
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Batalkan offboarding?</DialogTitle>
                    <DialogDescription>
                        Case menjadi CANCELLED dan tidak dapat dibuka kembali pada MVP. Employee, Contract, konteks exit, dan checklist snapshot tetap
                        dipertahankan sebagai histori.
                    </DialogDescription>
                </DialogHeader>
                <div className="space-y-2">
                    <Label htmlFor="offboarding-cancel-reason">Alasan pembatalan</Label>
                    <Textarea
                        id="offboarding-cancel-reason"
                        value={reason}
                        onChange={(event) => setReason(event.target.value)}
                        maxLength={2000}
                        placeholder="Alasan wajib"
                        autoFocus
                    />
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={processing}>
                            Kembali
                        </Button>
                    </DialogClose>
                    <Button type="button" variant="destructive" disabled={processing || reason.trim() === ''} onClick={cancel}>
                        {processing ? 'Membatalkan…' : 'Ya, batalkan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
