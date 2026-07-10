import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { CheckCircle2, Map, Save, Send } from 'lucide-react';
import type { FormEvent } from 'react';
import type { MapSettingForm, MapSettings } from '../types';

type Props = {
    can: { update: boolean };
    mapSettings: MapSettings;
    form: InertiaFormProps<MapSettingForm>;
    submit: (event: FormEvent) => void;
};

export function MapSettingsPanel({ can, mapSettings, form, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-emerald flex size-10 items-center justify-center rounded-md">
                        <Map className="size-5" />
                    </span>
                    Google Maps
                </CardTitle>
                <CardDescription>Konfigurasi peta untuk modul yang membutuhkan titik lokasi, geofencing, atau preview area.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Status Map" value={form.data.enabled ? 'Aktif' : 'Nonaktif'} />
                        <SummaryTile label="API Key" value={mapSettings.configured ? 'Tersimpan' : 'Belum diisi'} />
                        <SummaryTile label="Map ID" value={form.data.google_maps_map_id || 'Opsional'} />
                    </div>

                    <label className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                        <Checkbox
                            checked={form.data.enabled}
                            disabled={!can.update || form.processing}
                            onCheckedChange={(checked) => form.setData('enabled', Boolean(checked))}
                        />
                        <span>
                            <span className="flex items-center gap-2 text-sm font-medium">
                                Aktifkan Google Maps
                                {mapSettings.configured && <Badge variant="secondary">Configured</Badge>}
                            </span>
                            <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">
                                Jika aktif dan API Key tersedia, modul Work Locations dapat menampilkan map interaktif untuk memilih koordinat.
                            </span>
                        </span>
                    </label>

                    <div className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <FieldInfoLabel
                                htmlFor="google_maps_api_key"
                                description="API Key Google Maps JavaScript API. Key ini dipakai frontend untuk memuat peta pada modul lokasi."
                            >
                                Google Maps API Key
                            </FieldInfoLabel>
                            <Input
                                id="google_maps_api_key"
                                type="password"
                                value={form.data.google_maps_api_key}
                                disabled={!can.update || form.processing}
                                placeholder={mapSettings.configured ? 'API Key sudah tersimpan' : 'AIza...'}
                                onChange={(event) => form.setData('google_maps_api_key', event.target.value)}
                            />
                            <InputError message={form.errors.google_maps_api_key} />
                        </div>

                        <div className="space-y-2">
                            <FieldInfoLabel
                                htmlFor="google_maps_map_id"
                                description="Map ID Google untuk styling vector map atau Advanced Marker. Kosongkan jika belum memakai style custom."
                            >
                                Google Maps Map ID
                            </FieldInfoLabel>
                            <Input
                                id="google_maps_map_id"
                                value={form.data.google_maps_map_id}
                                disabled={!can.update || form.processing}
                                placeholder="Contoh: 8f348e..."
                                onChange={(event) => form.setData('google_maps_map_id', event.target.value)}
                            />
                            <InputError message={form.errors.google_maps_map_id} />
                        </div>
                    </div>

                    <div className="rounded-lg border border-dashed p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-sky flex size-9 shrink-0 items-center justify-center rounded-md">
                                <CheckCircle2 className="size-4" />
                            </span>
                            <div>
                                <p className="text-sm font-medium">Dipakai Modul Lokasi</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                                    Saat ini konfigurasi ini dipakai HR Work Locations untuk memilih latitude dan longitude. Ke depan bisa dipakai
                                    attendance geofence, lokasi kantor, area operasional, dan cabang project lain.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={!can.update || form.processing} className="h-11 min-w-40">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Maps
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function SummaryTile({ label, value }: { label: string; value: string | number }) {
    return (
        <div className="bg-background/60 rounded-lg border p-4">
            <p className="text-muted-foreground text-xs">{label}</p>
            <p className="mt-2 truncate text-lg font-semibold">{value}</p>
        </div>
    );
}
