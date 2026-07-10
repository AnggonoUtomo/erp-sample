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
                        Position akan masuk trash melalui soft delete. Data histori dan audit tetap dipertahankan untuk kebutuhan restore atau penelusuran.
                    </DialogDescription>
                </DialogHeader>

                {position && (
                    <div className="rounded-lg border bg-muted/30 p-3">
                        <p className="font-medium text-destructive">{position.name}</p>
                        <p className="mt-1 text-sm text-muted-foreground">
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
