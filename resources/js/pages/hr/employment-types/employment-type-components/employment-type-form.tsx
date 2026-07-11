import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { EmploymentTypeForm as EmploymentTypeFormData, EmploymentTypeRow } from '../types';

type Props = {
    form: InertiaFormProps<EmploymentTypeFormData>;
    editing: EmploymentTypeRow | null;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function EmploymentTypeForm({ form, editing, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="code"
                    required
                    description="Kode unik tipe hubungan kerja. Dipakai sebagai referensi employee contract, benefit, payroll, dan integrasi antar module."
                >
                    Code
                </FieldInfoLabel>
                <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="PERMANENT" />
                {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama tipe kerja yang tampil pada employee profile, kontrak, benefit, dan payroll."
                >
                    Nama Employment Type
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Permanent" />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="description" description="Catatan aturan kontrak, benefit, overtime, atau payroll untuk tipe kerja ini.">
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Contoh: hubungan kerja tetap untuk employee inti perusahaan"
                    rows={4}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-destructive text-sm">{form.errors.description}</p>}
            </div>

            <div className="grid gap-3">
                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox
                        checked={form.data.requires_contract_end_date}
                        onCheckedChange={(checked) => form.setData('requires_contract_end_date', checked === true)}
                    />
                    <span>
                        <span className="block font-medium">Wajib tanggal akhir kontrak</span>
                        <span className="text-muted-foreground mt-1 block text-xs leading-5">
                            Aktifkan untuk kontrak, magang, outsourcing, atau tipe kerja yang harus punya end date.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox
                        checked={form.data.included_in_payroll}
                        onCheckedChange={(checked) => form.setData('included_in_payroll', checked === true)}
                    />
                    <span>
                        <span className="block font-medium">Masuk payroll</span>
                        <span className="text-muted-foreground mt-1 block text-xs leading-5">
                            Aktifkan jika employee dengan tipe ini masuk proses payroll rutin atau perhitungan kompensasi internal.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox
                        checked={form.data.eligible_for_benefits}
                        onCheckedChange={(checked) => form.setData('eligible_for_benefits', checked === true)}
                    />
                    <span>
                        <span className="block font-medium">Eligible benefit</span>
                        <span className="text-muted-foreground mt-1 block text-xs leading-5">
                            Aktifkan jika tipe kerja ini berhak mengikuti benefit, allowance, atau fasilitas perusahaan.
                        </span>
                    </span>
                </label>

                <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                    <Checkbox
                        checked={form.data.eligible_for_overtime}
                        onCheckedChange={(checked) => form.setData('eligible_for_overtime', checked === true)}
                    />
                    <span>
                        <span className="block font-medium">Eligible overtime</span>
                        <span className="text-muted-foreground mt-1 block text-xs leading-5">
                            Aktifkan jika tipe kerja ini boleh masuk perhitungan lembur Attendance dan Payroll.
                        </span>
                    </span>
                </label>
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Employment Type aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Tipe aktif bisa dipilih di module employee dan dipakai oleh kontrak, benefit, Attendance, serta Payroll.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Employment Type'}
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
