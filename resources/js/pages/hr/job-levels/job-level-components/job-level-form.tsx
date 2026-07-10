import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { JobLevelForm as JobLevelFormData, JobLevelRow } from '../types';

type Props = {
    form: InertiaFormProps<JobLevelFormData>;
    editing: JobLevelRow | null;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function JobLevelForm({ form, editing, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="code"
                    required
                    description="Kode unik level jabatan. Dipakai sebagai referensi singkat pada employee profile, approval, payroll, dan integrasi antar module."
                >
                    Code
                </FieldInfoLabel>
                <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="L1" />
                {form.errors.code && <p className="text-sm text-destructive">{form.errors.code}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama level/grade yang tampil di tabel, pilihan employee, laporan headcount, dan aturan benefit/payroll."
                >
                    Nama Job Level
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Entry Level" />
                {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="description" description="Catatan fungsi level, scope tanggung jawab, atau contoh posisi yang termasuk dalam level ini.">
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Contoh: level awal untuk individual contributor junior"
                    rows={4}
                    className="min-h-24 w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-sm text-destructive">{form.errors.description}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Job level aktif</span>
                    <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                        Job level aktif bisa dipilih oleh module employee, approval, benefit, dan payroll. Nonaktifkan jika level tidak dipakai lagi.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Job Level'}
                    </Button>
                )}
                {editing && (
                    <Button type="button" variant="outline" onClick={onCancel}>
                        Batal
                    </Button>
                )}
            </div>
        </form>
    );
}
