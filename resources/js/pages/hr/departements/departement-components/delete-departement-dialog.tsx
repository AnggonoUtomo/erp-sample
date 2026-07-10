import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import type { DepartementRow } from '../types';

type Props = {
    departement: DepartementRow | null;
    form: InertiaFormProps<Record<string, never>>;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteDepartementDialog({ departement, form, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={!!departement} onOpenChange={(open) => !form.processing && onOpenChange(open)}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus Departement</DialogTitle>
                    <DialogDescription>
                        Departement yang memiliki child tidak bisa dihapus. Pastikan struktur organisasi sudah dipindahkan lebih dulu.
                    </DialogDescription>
                </DialogHeader>
                {departement && (
                    <div className="rounded-lg border border-destructive/30 bg-destructive/5 p-4">
                        <p className="font-medium text-destructive">{departement.name}</p>
                        <p className="mt-1 text-sm text-muted-foreground">{departement.code}</p>
                    </div>
                )}
                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={form.processing}>
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" onClick={onConfirm} disabled={form.processing}>
                        <Trash2 className="size-4" />
                        {form.processing ? 'Menghapus...' : 'Hapus'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
