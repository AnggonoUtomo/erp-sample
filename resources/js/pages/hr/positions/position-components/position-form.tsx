import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { DepartementOption, PositionForm, PositionRow } from '../types';

type Props = {
    form: InertiaFormProps<PositionForm>;
    editing: PositionRow | null;
    departementOptions: DepartementOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function PositionForm({ form, editing, departementOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="departement_id"
                    required
                    description="Departement pemilik jabatan. Data ini dipakai untuk employee profile, approval, dan report headcount."
                >
                    Departement
                </FieldInfoLabel>
                <Select
                    value={form.data.departement_id || 'none'}
                    onValueChange={(value) => form.setData('departement_id', value === 'none' ? '' : value)}
                >
                    <SelectTrigger id="departement_id">
                        <SelectValue placeholder="Pilih departement" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Pilih departement</SelectItem>
                        {departementOptions.map((option) => (
                            <SelectItem key={option.id} value={String(option.id)}>
                                {option.code} - {option.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {form.errors.departement_id && <p className="text-destructive text-sm">{form.errors.departement_id}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="code"
                    required
                    description="Kode unik jabatan. Dipakai untuk pencarian, import/export, dan referensi integrasi ke Attendance/Payroll."
                >
                    Code
                </FieldInfoLabel>
                <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="HR-MGR" />
                {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama jabatan yang akan tampil di profil karyawan, approval, dan laporan organisasi."
                >
                    Nama Position
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="HR Manager" />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="description"
                    description="Ringkasan fungsi jabatan. Berguna sebagai dokumentasi awal sebelum module job description dibuat."
                >
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Tanggung jawab utama jabatan"
                    rows={4}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-destructive text-sm">{form.errors.description}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Position aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Position aktif bisa dipakai oleh module employee. Nonaktifkan jika jabatan tidak digunakan lagi, tanpa menghapus riwayat.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Position'}
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
