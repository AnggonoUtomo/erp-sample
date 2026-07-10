import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { CreateRoleFormData } from '@/pages/console/access-control/types';
import { useForm } from '@inertiajs/react';
import { Save, X } from 'lucide-react';
import type { FormEvent } from 'react';

interface AddRoleDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function AddRoleDialog({ open, onOpenChange }: AddRoleDialogProps) {
    const { data, setData, post, processing, errors, reset } = useForm<CreateRoleFormData>({
        name: '',
        guard_name: 'web',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        post(route('access-control.roles.store'), {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                reset();
                onOpenChange(false);
            },
        });
    };

    if (!open) {
        return null;
    }

    return (
        <div className="bg-background/80 fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div className="bg-background w-full max-w-md rounded-lg border shadow-lg">
                <div className="flex items-center justify-between border-b px-4 py-3">
                    <div>
                        <h2 className="text-sm font-semibold">Add Role</h2>
                        <p className="text-muted-foreground mt-1 text-xs">Tambahkan role baru untuk access control.</p>
                    </div>
                    <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onOpenChange(false)} disabled={processing}>
                        <X className="size-4" />
                    </Button>
                </div>

                <form onSubmit={submit} className="space-y-4 p-4">
                    <div className="space-y-1.5">
                        <FieldInfoLabel
                            htmlFor="role-name"
                            required
                            className="text-xs font-medium"
                            description="Nama role disarankan memakai format singkat dan konsisten, misalnya admin-ops."
                        >
                            Nama Role
                        </FieldInfoLabel>
                        <Input
                            id="role-name"
                            value={data.name}
                            onChange={(event) => setData('name', event.target.value)}
                            placeholder="Contoh: admin-ops, finance-admin"
                            autoFocus
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="space-y-1.5">
                        <FieldInfoLabel
                            htmlFor="guard-name"
                            required
                            className="text-xs font-medium"
                            description="Guard menentukan area autentikasi Spatie Permission. Untuk aplikasi ini gunakan web."
                        >
                            Guard Name
                        </FieldInfoLabel>
                        <select
                            id="guard-name"
                            value={data.guard_name}
                            onChange={(event) => setData('guard_name', event.target.value)}
                            className="bg-background focus:border-primary focus:ring-primary/20 h-9 w-full rounded-lg border px-3 text-xs transition outline-none focus:ring-2"
                        >
                            <option value="web">web</option>
                        </select>
                        <InputError message={errors.guard_name} />
                    </div>

                    <div className="flex justify-end gap-2 pt-2">
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)} disabled={processing}>
                            Batal
                        </Button>
                        <Button type="submit" disabled={processing}>
                            <Save className="size-4" />
                            {processing ? 'Menyimpan...' : 'Simpan Role'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}
