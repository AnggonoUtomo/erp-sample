import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { WorkLocationForm as WorkLocationFormData, WorkLocationPageProps, WorkLocationRow } from '../types';
import { WorkLocationMapPanel } from './work-location-map-panel';

type Props = {
    form: InertiaFormProps<WorkLocationFormData>;
    editing: WorkLocationRow | null;
    canCreate: boolean;
    canUpdate: boolean;
    mapSettings: WorkLocationPageProps['mapSettings'];
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function WorkLocationForm({ form, editing, canCreate, canUpdate, mapSettings, onSubmit, onCancel }: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="code"
                        required
                        description="Kode unik lokasi kerja. Dipakai untuk employee profile, attendance, payroll area, dan integrasi."
                    >
                        Code
                    </FieldInfoLabel>
                    <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="HQ-JKT" />
                    {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
                </div>

                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="timezone"
                        required
                        description="Timezone lokasi kerja. Penting untuk attendance, shift, scheduler, dan cut off payroll."
                    >
                        Timezone
                    </FieldInfoLabel>
                    <Input
                        id="timezone"
                        value={form.data.timezone}
                        onChange={(event) => form.setData('timezone', event.target.value)}
                        placeholder="Asia/Jakarta"
                    />
                    {form.errors.timezone && <p className="text-destructive text-sm">{form.errors.timezone}</p>}
                </div>
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="name"
                    required
                    description="Nama lokasi kerja yang tampil pada employee profile, filter attendance, payroll, dan laporan headcount."
                >
                    Nama Lokasi
                </FieldInfoLabel>
                <Input
                    id="name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    placeholder="Head Office Jakarta"
                />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="address"
                    description="Alamat lengkap lokasi kerja. Berguna untuk data kantor, kontrak kerja, dan referensi operasional."
                >
                    Alamat
                </FieldInfoLabel>
                <textarea
                    id="address"
                    value={form.data.address}
                    onChange={(event) => form.setData('address', event.target.value)}
                    placeholder="Jl. Jend. Sudirman Kav. 52-53"
                    rows={3}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-20 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.address && <p className="text-destructive text-sm">{form.errors.address}</p>}
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="city" description="Kota lokasi kerja. Dipakai sebagai filter cepat pada tabel dan laporan.">
                        Kota
                    </FieldInfoLabel>
                    <Input id="city" value={form.data.city} onChange={(event) => form.setData('city', event.target.value)} placeholder="Jakarta" />
                    {form.errors.city && <p className="text-destructive text-sm">{form.errors.city}</p>}
                </div>

                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="province" description="Provinsi atau wilayah administrasi lokasi kerja.">
                        Provinsi
                    </FieldInfoLabel>
                    <Input
                        id="province"
                        value={form.data.province}
                        onChange={(event) => form.setData('province', event.target.value)}
                        placeholder="DKI Jakarta"
                    />
                    {form.errors.province && <p className="text-destructive text-sm">{form.errors.province}</p>}
                </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="country"
                        required
                        description="Negara lokasi kerja. Default Indonesia, tetapi bisa disesuaikan untuk cabang regional."
                    >
                        Negara
                    </FieldInfoLabel>
                    <Input
                        id="country"
                        value={form.data.country}
                        onChange={(event) => form.setData('country', event.target.value)}
                        placeholder="Indonesia"
                    />
                    {form.errors.country && <p className="text-destructive text-sm">{form.errors.country}</p>}
                </div>

                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="postal_code" description="Kode pos lokasi kerja jika tersedia.">
                        Kode Pos
                    </FieldInfoLabel>
                    <Input
                        id="postal_code"
                        value={form.data.postal_code}
                        onChange={(event) => form.setData('postal_code', event.target.value)}
                        placeholder="12190"
                    />
                    {form.errors.postal_code && <p className="text-destructive text-sm">{form.errors.postal_code}</p>}
                </div>
            </div>

            <WorkLocationMapPanel form={form} mapSettings={mapSettings} />

            {(form.errors.latitude || form.errors.longitude || form.errors.geofence_radius_meters) && (
                <div className="border-destructive/30 bg-destructive/5 text-destructive rounded-lg border p-3 text-sm">
                    {form.errors.latitude || form.errors.longitude || form.errors.geofence_radius_meters}
                </div>
            )}

            <div className="space-y-2">
                <FieldInfoLabel
                    htmlFor="description"
                    description="Catatan fungsi lokasi, cakupan operasional, atau informasi tambahan untuk admin HR."
                >
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    placeholder="Kantor pusat untuk fungsi manajemen dan operation support"
                    rows={4}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                />
                {form.errors.description && <p className="text-destructive text-sm">{form.errors.description}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Lokasi aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Lokasi aktif bisa dipilih pada employee profile, attendance, dan payroll. Nonaktifkan jika lokasi sudah tidak digunakan.
                    </span>
                </span>
            </label>

            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Work Location'}
                    </Button>
                )}
                <Button type="button" variant="outline" disabled={form.processing} onClick={onCancel}>
                    Batal
                </Button>
            </div>
        </form>
    );
}
