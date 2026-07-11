import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { OrganizationStructureRow } from '../types';

type Props = {
    organizationStructure: OrganizationStructureRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteOrganizationStructureDialog({ organizationStructure, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(organizationStructure)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus Permanen Structure?' : 'Arsipkan Structure?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? `Structure ${organizationStructure?.name ?? ''} akan dihapus permanen.`
                            : `Structure ${organizationStructure?.name ?? ''} akan dipindahkan ke arsip. Child aktif harus dipindahkan/nonaktifkan terlebih dahulu.`}
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
