import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Clock3, Save, Send } from 'lucide-react';
import type { FormEvent } from 'react';
import { dateFormatOptions, timeFormatOptions, timezoneOptions } from '../options';
import type { LocalizationForm, LocalizationSettings } from '../types';

type Props = {
    can: { update: boolean };
    localizationSettings: LocalizationSettings;
    form: InertiaFormProps<LocalizationForm>;
    submit: (event: FormEvent) => void;
};

export function LocalizationSettingsPanel({ can, localizationSettings, form, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-sky flex size-10 items-center justify-center rounded-md">
                        <Clock3 className="size-5" />
                    </span>
                    Timezone & Format Tanggal
                </CardTitle>
                <CardDescription>Atur zona waktu dan format tampilan tanggal untuk aplikasi.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Tanggal" value={localizationSettings.preview_date} />
                        <SummaryTile label="Jam" value={localizationSettings.preview_time} />
                        <SummaryTile label="Timezone Aktif" value={localizationSettings.timezone} />
                    </div>

                    <div className="grid gap-4 lg:grid-cols-2">
                        <div className="space-y-2">
                            <FieldInfoLabel required description="Zona waktu utama aplikasi. Ini memengaruhi tanggal/jam runtime setelah disimpan.">
                                Timezone
                            </FieldInfoLabel>
                            <Select
                                value={form.data.timezone}
                                onValueChange={(value) => form.setData('timezone', value)}
                                disabled={!can.update || form.processing}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih timezone" />
                                </SelectTrigger>
                                <SelectContent>
                                    {timezoneOptions.map((timezone) => (
                                        <SelectItem key={timezone} value={timezone}>
                                            {timezone}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.timezone} />
                        </div>

                        <div className="space-y-2">
                            <FieldInfoLabel description="Gabungan format tanggal dan jam yang akan dipakai sebagai default tampilan.">
                                Preview Format
                            </FieldInfoLabel>
                            <div className="border-input bg-muted/40 flex min-h-10 items-center rounded-md border px-3 text-sm">
                                {localizationSettings.preview_datetime}
                            </div>
                        </div>

                        <FormatSelect
                            label="Format Tanggal"
                            description="Format tanggal default untuk tabel, detail, dan laporan."
                            value={form.data.date_format}
                            disabled={!can.update || form.processing}
                            options={dateFormatOptions}
                            error={form.errors.date_format}
                            onChange={(value) => form.setData('date_format', value)}
                        />
                        <FormatSelect
                            label="Format Jam"
                            description="Format jam default, 24 jam atau 12 jam dengan AM/PM."
                            value={form.data.time_format}
                            disabled={!can.update || form.processing}
                            options={timeFormatOptions}
                            error={form.errors.time_format}
                            onChange={(value) => form.setData('time_format', value)}
                        />
                    </div>

                    <div className="rounded-lg border border-dashed p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-indigo flex size-9 shrink-0 items-center justify-center rounded-md">
                                <Clock3 className="size-4" />
                            </span>
                            <div>
                                <p className="text-sm font-medium">Format Tersimpan</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                                    Format aktif saat ini adalah <span className="font-medium">{localizationSettings.datetime_format}</span>.
                                    Setelah disimpan, modul lain bisa membaca format ini dari shared props Inertia.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={!can.update || form.processing} className="h-11 min-w-44">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Timezone
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function SummaryTile({ label, value }: { label: string; value: string }) {
    return (
        <div className="bg-background/60 rounded-lg border p-4">
            <p className="text-muted-foreground text-xs">{label}</p>
            <p className="mt-2 text-lg font-semibold">{value}</p>
        </div>
    );
}

function FormatSelect({
    label,
    description,
    value,
    disabled,
    options,
    error,
    onChange,
}: {
    label: string;
    description: string;
    value: string;
    disabled: boolean;
    options: { value: string; label: string }[];
    error?: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <FieldInfoLabel required description={description}>
                {label}
            </FieldInfoLabel>
            <Select value={value} onValueChange={onChange} disabled={disabled}>
                <SelectTrigger>
                    <SelectValue placeholder={`Pilih ${label.toLowerCase()}`} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            <InputError message={error} />
        </div>
    );
}
