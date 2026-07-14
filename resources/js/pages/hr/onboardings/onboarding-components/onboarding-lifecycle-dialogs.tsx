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
import { CheckCircle2, XCircle } from 'lucide-react';
import { useState } from 'react';

export function canCompleteOnboarding(status: string, archived: boolean, requiredIncomplete: number, permitted: boolean) {
    return permitted && !archived && status === 'IN_PROGRESS' && requiredIncomplete === 0;
}

export function canCancelOnboarding(status: string, archived: boolean, permitted: boolean) {
    return permitted && !archived && (status === 'DRAFT' || status === 'IN_PROGRESS');
}

export function CompleteOnboardingDialog({ onboardingId }: { onboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const submit = () => {
        setProcessing(true);
        router.patch(
            route('hr.onboardings.complete', onboardingId),
            {},
            { preserveScroll: true, onSuccess: () => setOpen(false), onFinish: () => setProcessing(false) },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm">
                    <CheckCircle2 className="size-4" /> Selesaikan
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Selesaikan onboarding?</DialogTitle>
                    <DialogDescription>Semua task wajib sudah terminal. Setelah selesai, checklist tidak dapat diubah lagi.</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Kembali</Button>
                    </DialogClose>
                    <Button onClick={submit} disabled={processing}>
                        Konfirmasi selesai
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

export function CancelOnboardingDialog({ onboardingId }: { onboardingId: number }) {
    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [processing, setProcessing] = useState(false);

    const submit = () => {
        if (!reason.trim()) return;
        setProcessing(true);
        router.patch(
            route('hr.onboardings.cancel', onboardingId),
            { reason: reason.trim() },
            { preserveScroll: true, onSuccess: () => setOpen(false), onFinish: () => setProcessing(false) },
        );
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="destructive">
                    <XCircle className="size-4" /> Batalkan
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Batalkan onboarding?</DialogTitle>
                    <DialogDescription>Alasan disimpan sebagai evidence audit dan onboarding tidak dapat diaktifkan kembali.</DialogDescription>
                </DialogHeader>
                <div className="space-y-2">
                    <Label htmlFor="cancel-reason">Alasan pembatalan</Label>
                    <Textarea id="cancel-reason" value={reason} maxLength={2000} onChange={(event) => setReason(event.target.value)} autoFocus />
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Kembali</Button>
                    </DialogClose>
                    <Button variant="destructive" onClick={submit} disabled={processing || !reason.trim()}>
                        Konfirmasi batal
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
