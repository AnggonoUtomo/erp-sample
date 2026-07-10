import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { ReferenceDataRow } from '../types';

type Props = {
    referenceData: ReferenceDataRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteHRReferenceDataDialog({ referenceData, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(referenceData)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus Permanen Reference Data?' : 'Arsipkan Reference Data?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? `Reference data ${referenceData?.name ?? ''} akan dihapus permanen dan tidak bisa dipulihkan dari sistem.`
                            : `Reference data ${referenceData?.name ?? ''} akan dipindahkan ke arsip. Data masih bisa dipulihkan oleh user yang punya permission restore.`}
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
