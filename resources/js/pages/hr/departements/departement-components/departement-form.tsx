import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { DepartementForm, DepartementOption, DepartementRow } from '../types';

type Props = {
    form: InertiaFormProps<DepartementForm>;
    editing: DepartementRow | null;
    parentOptions: DepartementOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function DepartementForm({ form, editing, parentOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="code"
                    required
                    description="Kode unik Departement. Dipakai untuk pencarian cepat, laporan, import/export, dan referensi antar module."
                >
                    Code
                </FieldInfoLabel>
                <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="HRD" />
                {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama Departement yang tampil di tabel, pilihan employee, struktur organisasi, dan laporan headcount."
                >
                    Nama Departement
                </FieldInfoLabel>
                <Input
                    id="name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    placeholder="Human Resources"
                />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="parent_id"
                    description="Parent menentukan hierarchy. Pilih Root Departement jika unit ini berada di level paling atas."
                >
                    Parent Departement
                </FieldInfoLabel>
                <Select value={form.data.parent_id || 'none'} onValueChange={(value) => form.setData('parent_id', value === 'none' ? '' : value)}>
                    <SelectTrigger id="parent_id">
                        <SelectValue placeholder="Pilih parent" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Root departement</SelectItem>
                        {parentOptions.map((option) => (
                            <SelectItem key={option.id} value={String(option.id)}>
                                {option.code} - {option.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {form.errors.parent_id && <p className="text-destructive text-sm">{form.errors.parent_id}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="description"
                    description="Catatan singkat tentang fungsi Departement. Berguna untuk admin baru, audit, dan dokumentasi organisasi."
                >
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Fungsi utama departement"
                    rows={4}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-destructive text-sm">{form.errors.description}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Departement aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Departement aktif bisa dipilih oleh module lain. Nonaktifkan jika Departement sudah tidak digunakan, tanpa menghapus riwayat.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Departement'}
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
