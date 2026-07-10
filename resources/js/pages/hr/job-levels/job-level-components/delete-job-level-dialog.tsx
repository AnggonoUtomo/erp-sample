import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { JobLevelRow } from '../types';

type Props = {
    jobLevel: JobLevelRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteJobLevelDialog({ jobLevel, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(jobLevel)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus Permanen Job Level?' : 'Arsipkan Job Level?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? `Job level ${jobLevel?.name ?? ''} akan dihapus permanen dan tidak bisa dipulihkan dari sistem.`
                            : `Job level ${jobLevel?.name ?? ''} akan dipindahkan ke arsip. Data masih bisa dipulihkan oleh user yang punya permission restore.`}
                    </DialogDescription>
                </DialogHeader>

                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" disabled={form.processing} onClick={onConfirm}>
                        {form.processing ? 'Memproses...' : permanent ? 'Hapus Permanen' : 'Arsipkan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
