import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { EmploymentStatusForm as EmploymentStatusFormData, EmploymentStatusRow } from '../types';

type Props = {
    form: InertiaFormProps<EmploymentStatusFormData>;
    editing: EmploymentStatusRow | null;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function EmploymentStatusForm({ form, editing, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="code"
                    required
                    description="Kode unik status kerja. Dipakai sebagai referensi singkat untuk employee lifecycle, attendance, payroll, dan integrasi antar module."
                >
                    Code
                </FieldInfoLabel>
                <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="PROBATION" />
                {form.errors.code && <p className="text-sm text-destructive">{form.errors.code}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama status kerja yang tampil pada employee profile, filter HR, attendance, dan payroll."
                >
                    Nama Employment Status
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Probation" />
                {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="description" description="Catatan fungsi status, aturan lifecycle, atau dampaknya terhadap attendance dan payroll.">
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Contoh: karyawan dalam masa percobaan dan masih mengikuti evaluasi awal"
                    rows={4}
                    className="min-h-24 w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-sm text-destructive">{form.errors.description}</p>}
            </div>

            <div className="grid gap-3">
                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox checked={form.data.requires_attendance} onCheckedChange={(checked) => form.setData('requires_attendance', checked === true)} />
                    <span>
                        <span className="block font-medium">Wajib attendance</span>
                        <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                            Aktifkan jika employee dengan status ini tetap wajib check-in/check-out atau masuk perhitungan kehadiran.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox checked={form.data.included_in_payroll} onCheckedChange={(checked) => form.setData('included_in_payroll', checked === true)} />
                    <span>
                        <span className="block font-medium">Masuk payroll</span>
                        <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                            Aktifkan jika employee dengan status ini masuk proses payroll rutin atau perhitungan kompensasi.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox checked={form.data.is_final_status} onCheckedChange={(checked) => form.setData('is_final_status', checked === true)} />
                    <span>
                        <span className="block font-medium">Status akhir</span>
                        <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                            Tandai untuk status seperti resigned atau terminated, yaitu employee tidak lagi aktif secara lifecycle.
                        </span>
                    </span>
                </label>
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Employment Status aktif</span>
                    <span className="mt-1 block text-xs leading-5 text-muted-foreground">
                        Status aktif bisa dipilih di module employee dan dipakai oleh Attendance serta Payroll. Nonaktifkan jika status tidak dipakai lagi.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Employment Status'}
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
