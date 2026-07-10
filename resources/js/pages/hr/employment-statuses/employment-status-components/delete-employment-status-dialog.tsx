import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { EmploymentStatusRow } from '../types';

type Props = {
    employmentStatus: EmploymentStatusRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteEmploymentStatusDialog({ employmentStatus, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(employmentStatus)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus Permanen Employment Status?' : 'Arsipkan Employment Status?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? `Employment Status ${employmentStatus?.name ?? ''} akan dihapus permanen dan tidak bisa dipulihkan dari sistem.`
                            : `Employment Status ${employmentStatus?.name ?? ''} akan dipindahkan ke arsip. Data masih bisa dipulihkan oleh user yang punya permission restore.`}
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
