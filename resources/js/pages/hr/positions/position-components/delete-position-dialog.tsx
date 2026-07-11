import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { PositionRow } from '../types';

type Props = {
    position: PositionRow | null;
    form: InertiaFormProps<Record<string, never>>;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeletePositionDialog({ position, form, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={!!position} onOpenChange={(open) => !form.processing && onOpenChange(open)}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Hapus Position?</DialogTitle>
                    <DialogDescription>
                        Position akan masuk trash melalui soft delete. Data histori dan audit tetap dipertahankan untuk kebutuhan restore atau
                        penelusuran.
                    </DialogDescription>
                </DialogHeader>

                {position && (
                    <div className="bg-muted/30 rounded-lg border p-3">
                        <p className="text-destructive font-medium">{position.name}</p>
                        <p className="text-muted-foreground mt-1 text-sm">
                            {position.code} - {position.departement?.name ?? 'Tanpa departement'}
                        </p>
                    </div>
                )}

                <DialogFooter>
                    <Button type="button" variant="outline" disabled={form.processing} onClick={() => onOpenChange(false)}>
                        Batal
                    </Button>
                    <Button type="button" variant="destructive" disabled={form.processing} onClick={onConfirm}>
                        {form.processing ? 'Menghapus...' : 'Hapus Position'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
