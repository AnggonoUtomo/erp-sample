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
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { RotateCcw, SkipForward } from 'lucide-react';
import { useState } from 'react';

export function OnboardingTaskReasonDialog({ action, onboardingId, taskId }: { action: 'skip' | 'reopen'; onboardingId: number; taskId: number }) {
    const [open, setOpen] = useState(false);
    const [reason, setReason] = useState('');
    const [processing, setProcessing] = useState(false);
    const isSkip = action === 'skip';

    const submit = () => {
        const field = isSkip ? 'skip_reason' : 'reopen_reason';
        router.patch(
            route(`hr.onboardings.tasks.${action}`, { onboarding: onboardingId, task: taskId }),
            { [field]: reason.trim() },
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
                <Button size="sm" variant="outline">
                    {isSkip ? <SkipForward className="size-4" /> : <RotateCcw className="size-4" />}
                    {isSkip ? 'Skip' : 'Buka kembali'}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{isSkip ? 'Skip task?' : 'Buka kembali task?'}</DialogTitle>
                    <DialogDescription>
                        {isSkip
                            ? 'Alasan wajib dicatat. Required task memerlukan permission khusus.'
                            : 'Task kembali ke PENDING dan evidence terminal aktif akan dibersihkan secara terkontrol.'}
                    </DialogDescription>
                </DialogHeader>
                <Textarea value={reason} onChange={(event) => setReason(event.target.value)} maxLength={2000} placeholder="Alasan wajib" />
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={processing}>
                            Batal
                        </Button>
                    </DialogClose>
                    <Button type="button" disabled={processing || reason.trim() === ''} onClick={submit}>
                        {processing ? 'Menyimpan…' : 'Konfirmasi'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
