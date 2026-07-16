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
import { Archive, ArchiveRestore } from 'lucide-react';
import { useState } from 'react';

export function OffboardingArchiveDialog({ offboardingId, archived }: { offboardingId: number; archived: boolean }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);
    const submit = () => {
        setProcessing(true);
        const options = { preserveScroll: true, onSuccess: () => setOpen(false), onFinish: () => setProcessing(false) };
        if (archived) router.patch(route('hr.offboardings.restore', offboardingId), {}, options);
        else router.delete(route('hr.offboardings.archive', offboardingId), options);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                    {archived ? <ArchiveRestore className="size-4" /> : <Archive className="size-4" />}
                    {archived ? 'Restore' : 'Arsipkan'}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{archived ? 'Restore histori offboarding?' : 'Arsipkan offboarding?'}</DialogTitle>
                    <DialogDescription>
                        {archived
                            ? 'Histori terminal akan kembali tampil pada daftar aktif.'
                            : 'Hanya offboarding terminal yang dapat diarsipkan. Data dan audit tidak dihapus.'}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">Kembali</Button>
                    </DialogClose>
                    <Button onClick={submit} disabled={processing}>
                        Konfirmasi
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
