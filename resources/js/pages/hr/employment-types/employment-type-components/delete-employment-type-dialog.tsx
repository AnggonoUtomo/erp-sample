import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { EmploymentTypeRow } from '../types';

type Props = {
    employmentType: EmploymentTypeRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteEmploymentTypeDialog({ employmentType, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(employmentType)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus Permanen Employment Type?' : 'Arsipkan Employment Type?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? `Employment Type ${employmentType?.name ?? ''} akan dihapus permanen dan tidak bisa dipulihkan dari sistem.`
                            : `Employment Type ${employmentType?.name ?? ''} akan dipindahkan ke arsip. Data masih bisa dipulihkan oleh user yang punya permission restore.`}
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
