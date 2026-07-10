import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { InertiaFormProps } from '@inertiajs/react';
import type { EmployeeRow } from '../types';

type Props = {
    employee: EmployeeRow | null;
    form: InertiaFormProps<Record<string, never>>;
    permanent: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
};

export function DeleteEmployeeDialog({ employee, form, permanent, onOpenChange, onConfirm }: Props) {
    return (
        <Dialog open={Boolean(employee)} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{permanent ? 'Hapus permanen employee?' : 'Arsipkan employee?'}</DialogTitle>
                    <DialogDescription>
                        {permanent
                            ? 'Data employee akan dihapus permanen. Gunakan hanya untuk data yang benar-benar salah input dan belum dipakai transaksi.'
                            : 'Employee akan dipindahkan ke arsip dan bisa dipulihkan kembali jika dibutuhkan.'}
                    </DialogDescription>
                </DialogHeader>
                {employee && (
                    <div className="flex items-center gap-3 rounded-lg border p-3">
                        <Avatar className="size-11 rounded-lg">
                            <AvatarImage src={employee.avatar ?? undefined} alt={employee.display_name} />
                            <AvatarFallback className="rounded-lg">{employee.display_name.slice(0, 2).toUpperCase()}</AvatarFallback>
                        </Avatar>
                        <div>
                            <p className="font-medium">{employee.display_name}</p>
                            <p className="text-sm text-muted-foreground">{employee.employee_number}</p>
                        </div>
                    </div>
                )}
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
