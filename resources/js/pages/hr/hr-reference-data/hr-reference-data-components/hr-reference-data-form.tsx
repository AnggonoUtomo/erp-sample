import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { ReferenceDataForm as ReferenceDataFormData, ReferenceDataOption, ReferenceDataRow } from '../types';

type Props = {
    form: InertiaFormProps<ReferenceDataFormData>;
    editing: ReferenceDataRow | null;
    categoryOptions: ReferenceDataOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function HRReferenceDataForm({ form, editing, categoryOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="category"
                        required
                        description="Kategori referensi, misalnya gender, marital-status, education-level, religion, bank, atau blood-type."
                    >
                        Kategori
                    </FieldInfoLabel>
                    <Select value={form.data.category || undefined} onValueChange={(value) => form.setData('category', value)}>
                        <SelectTrigger id="category">
                            <SelectValue placeholder="Pilih kategori" />
                        </SelectTrigger>
                        <SelectContent>
                            {categoryOptions.map((option) => (
                                <SelectItem key={option.value} value={option.value}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {form.errors.category && <p className="text-destructive text-sm">{form.errors.category}</p>}
                </div>

                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="code"
                        required
                        description="Kode unik di dalam kategori. Gunakan format singkat dan konsisten, misalnya MALE, S1, atau BCA."
                    >
                        Code
                    </FieldInfoLabel>
                    <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="MALE" />
                    {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
                </div>
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="name" required description="Nama referensi yang akan tampil di form employee, report, atau dropdown HR.">
                    Nama Reference Data
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Male" />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="description" description="Keterangan fungsi referensi atau aturan pemakaiannya di modul HR lain.">
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Contoh: pilihan gender untuk employee profile"
                    rows={3}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-20 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-destructive text-sm">{form.errors.description}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Reference data aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Data aktif bisa dipilih pada form employee dan dipakai oleh modul HR lain.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(editing ? canUpdate : canCreate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Reference Data'}
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
