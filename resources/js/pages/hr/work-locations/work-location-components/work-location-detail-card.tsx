import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { CalendarClock, Clock3, MapPin } from 'lucide-react';
import type { WorkLocationRow } from '../types';

type Props = {
    workLocation: WorkLocationRow | null;
};

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

export function WorkLocationDetailCard({ workLocation }: Props) {
    if (!workLocation) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <MapPin className="size-4" />
                        </span>
                        Work Location Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="text-muted-foreground space-y-3 text-sm">
                    <p>Pilih salah satu lokasi kerja pada tabel untuk melihat detailnya di panel ini.</p>
                    <p>Lokasi kerja dipakai sebagai referensi employee profile, attendance area, payroll, dan laporan organisasi.</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card data-dashboard-card>
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <MapPin className="size-4" />
                            </span>
                            {workLocation.name}
                        </CardTitle>
                        <p className="text-muted-foreground mt-2 text-sm">Kode: {workLocation.code}</p>
                    </div>
                    {workLocation.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={workLocation.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {workLocation.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4 p-5">
                <div className="grid gap-3">
                    <ReadonlyField label="Code" value={workLocation.code} />
                    <ReadonlyField label="Nama Lokasi" value={workLocation.name} />
                </div>

                <div className="space-y-2">
                    <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Alamat</p>
                    <textarea
                        disabled
                        value={workLocation.address || 'Alamat belum diisi.'}
                        rows={3}
                        className="border-input bg-muted/40 text-muted-foreground min-h-20 w-full resize-none rounded-md border px-3 py-2 text-sm disabled:opacity-100"
                    />
                    <div className="grid gap-3 sm:grid-cols-2">
                        <ReadonlyField label="Kota" value={workLocation.city || 'Belum diisi'} />
                        <ReadonlyField label="Provinsi" value={workLocation.province || 'Belum diisi'} />
                        <ReadonlyField label="Negara" value={workLocation.country} />
                        <ReadonlyField label="Kode Pos" value={workLocation.postal_code || 'Belum diisi'} />
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-3">
                    <ReadonlyField label="Latitude" value={workLocation.latitude !== null ? String(workLocation.latitude) : 'Belum ditentukan'} />
                    <ReadonlyField label="Longitude" value={workLocation.longitude !== null ? String(workLocation.longitude) : 'Belum ditentukan'} />
                    <ReadonlyField
                        label="Radius Geofence"
                        value={workLocation.geofence_radius_meters !== null ? `${workLocation.geofence_radius_meters} meter` : 'Tidak digunakan'}
                    />
                </div>

                <div className="space-y-2">
                    <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Deskripsi</p>
                    <textarea
                        disabled
                        value={workLocation.description || 'Belum ada deskripsi untuk lokasi kerja ini.'}
                        rows={4}
                        className="border-input bg-muted/40 text-muted-foreground min-h-24 w-full resize-none rounded-md border px-3 py-2 text-sm disabled:opacity-100"
                    />
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <Clock3 className="text-muted-foreground mt-0.5 size-4" />
                        <div>
                            <p className="text-sm font-medium">{workLocation.timezone}</p>
                            <p className="text-muted-foreground mt-1 text-xs leading-5">
                                Timezone ini menjadi basis waktu attendance, shift, scheduler, cut off payroll, dan aktivitas operasional lokasi.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="text-muted-foreground mt-0.5 size-4" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {formatDate(workLocation.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {formatDate(workLocation.updated_at)}
                            </p>
                            {workLocation.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {formatDate(workLocation.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function ReadonlyField({ label, value }: { label: string; value: string }) {
    return (
        <div className="space-y-1.5">
            <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">{label}</p>
            <Input disabled value={value} className="bg-muted/40 text-sm disabled:opacity-100" />
        </div>
    );
}
