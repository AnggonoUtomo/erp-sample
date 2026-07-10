import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Power, Save, Send } from 'lucide-react';
import type { Dispatch, FormEvent, SetStateAction } from 'react';
import { maintenanceStyleOptions, retryUnitOptions } from '../options';
import type { MaintenanceMode, MaintenanceModeForm, RetryUnit } from '../types';
import { formatRetryDuration, retryPartsToSeconds } from '../utils';

type Props = {
    can: { update: boolean };
    maintenanceMode: MaintenanceMode;
    form: InertiaFormProps<MaintenanceModeForm>;
    retryAmount: string;
    retryUnit: RetryUnit;
    retrySecondsPreview: number | null;
    retryBreakdownPreview: string | null;
    retryIsOutOfRange: boolean;
    setRetryAmount: Dispatch<SetStateAction<string>>;
    setRetryUnit: Dispatch<SetStateAction<RetryUnit>>;
    submit: (event: FormEvent) => void;
};

export function MaintenanceModePanel({
    can,
    maintenanceMode,
    form,
    retryAmount,
    retryUnit,
    retrySecondsPreview,
    retryBreakdownPreview,
    retryIsOutOfRange,
    setRetryAmount,
    setRetryUnit,
    submit,
}: Props) {
    const disabled = !can.update || form.processing;

    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-amber flex size-10 items-center justify-center rounded-md">
                        <Power className="size-5" />
                    </span>
                    Maintenance Mode
                </CardTitle>
                <CardDescription>Aktifkan mode perawatan aplikasi dengan secret bypass untuk admin.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="rounded-lg border border-dashed p-4">
                        <p className="text-sm font-medium">Cara Kerja Maintenance Mode</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                            Saat aktif, aplikasi ditutup sementara untuk user umum. Gunakan ini saat deploy, migrasi database, perbaikan urgent,
                            atau perawatan server. Admin/developer tetap bisa masuk melalui secret bypass jika disiapkan.
                        </p>
                    </div>

                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Stored Status" value={maintenanceMode.enabled ? 'On' : 'Off'} />
                        <SummaryTile label="Runtime Status" value={maintenanceMode.active ? 'Down' : 'Live'} />
                        <SummaryTile label="Retry" value={formatRetryDuration(maintenanceMode.retry_seconds)} />
                    </div>

                    <label className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                        <Checkbox checked={form.data.enabled} disabled={disabled} onCheckedChange={(checked) => form.setData('enabled', Boolean(checked))} />
                        <span>
                            <span className="block text-sm font-medium">Aktifkan maintenance mode</span>
                            <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">
                                Saat aktif, Laravel akan menjalankan mode down. Gunakan secret bypass agar admin tetap bisa masuk.
                            </span>
                        </span>
                    </label>

                    <div className="space-y-2">
                        <FieldInfoLabel description="Pesan yang tampil pada halaman maintenance.">Maintenance Message</FieldInfoLabel>
                        <textarea
                            value={form.data.message}
                            disabled={disabled}
                            rows={4}
                            className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus:ring-ring min-h-24 w-full rounded-md border px-3 py-2 text-sm focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                            placeholder="Aplikasi sedang dalam mode maintenance."
                            onChange={(event) => form.setData('message', event.target.value)}
                        />
                        <InputError message={form.errors.message} />
                    </div>

                    <div className="space-y-3">
                        <FieldInfoLabel description="Pilih visual halaman yang ditampilkan saat aplikasi masuk maintenance mode.">Style Halaman</FieldInfoLabel>
                        <Select
                            value={form.data.page_style}
                            disabled={disabled}
                            onValueChange={(value) => form.setData('page_style', value as MaintenanceModeForm['page_style'])}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Pilih style halaman maintenance" />
                            </SelectTrigger>
                            <SelectContent>
                                {maintenanceStyleOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <div className="grid gap-3 md:grid-cols-3">
                            {maintenanceStyleOptions.map((option) => (
                                <button
                                    key={option.value}
                                    type="button"
                                    disabled={disabled}
                                    onClick={() => form.setData('page_style', option.value as MaintenanceModeForm['page_style'])}
                                    className={
                                        form.data.page_style === option.value
                                            ? 'border-primary bg-primary/10 text-primary rounded-lg border p-3 text-left transition'
                                            : 'hover:bg-muted/60 rounded-lg border p-3 text-left transition'
                                    }
                                >
                                    <span className="block text-sm font-medium">{option.label}</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">{option.description}</span>
                                </button>
                            ))}
                        </div>
                        <InputError message={form.errors.page_style} />
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        <RetryField
                            form={form}
                            disabled={disabled}
                            retryAmount={retryAmount}
                            retryUnit={retryUnit}
                            retrySecondsPreview={retrySecondsPreview}
                            retryBreakdownPreview={retryBreakdownPreview}
                            retryIsOutOfRange={retryIsOutOfRange}
                            setRetryAmount={setRetryAmount}
                            setRetryUnit={setRetryUnit}
                        />
                        <div className="space-y-2">
                            <FieldInfoLabel description="Auto refresh browser saat maintenance. Kosongkan untuk nonaktif.">Refresh Seconds</FieldInfoLabel>
                            <Input
                                type="number"
                                min="5"
                                max="3600"
                                value={form.data.refresh_seconds}
                                disabled={disabled}
                                onChange={(event) => form.setData('refresh_seconds', event.target.value)}
                            />
                            <p className="text-muted-foreground text-xs leading-relaxed">
                                Membuat halaman maintenance auto reload berkala. Kosongkan jika user harus refresh manual.
                            </p>
                            <InputError message={form.errors.refresh_seconds} />
                        </div>
                        <div className="space-y-2">
                            <FieldInfoLabel description="Slug rahasia untuk bypass maintenance. Contoh: admin-bypass-2026.">Secret Bypass</FieldInfoLabel>
                            <Input
                                value={form.data.secret}
                                disabled={disabled}
                                placeholder="admin-bypass-2026"
                                onChange={(event) => form.setData('secret', event.target.value)}
                            />
                            <p className="text-muted-foreground text-xs leading-relaxed">
                                URL rahasia untuk melewati maintenance dari browser admin, misalnya /admin-bypass-2026.
                            </p>
                            <InputError message={form.errors.secret} />
                        </div>
                    </div>

                    {maintenanceMode.bypass_url ? (
                        <div className="rounded-lg border border-dashed p-4">
                            <p className="text-sm font-medium">Bypass URL</p>
                            <p className="text-muted-foreground mt-1 text-xs leading-relaxed break-all">{maintenanceMode.bypass_url}</p>
                            <p className="text-muted-foreground mt-2 text-xs leading-relaxed">
                                Buka URL ini sekali dari browser admin untuk mendapatkan cookie bypass. Setelah itu browser tersebut bisa mengakses
                                aplikasi walaupun maintenance mode aktif.
                            </p>
                        </div>
                    ) : null}

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={disabled} className="h-11 min-w-44">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Maintenance
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
            <p className="mt-2 text-2xl font-semibold">{value}</p>
        </div>
    );
}

function RetryField({
    form,
    disabled,
    retryAmount,
    retryUnit,
    retrySecondsPreview,
    retryBreakdownPreview,
    retryIsOutOfRange,
    setRetryAmount,
    setRetryUnit,
}: {
    form: InertiaFormProps<MaintenanceModeForm>;
    disabled: boolean;
    retryAmount: string;
    retryUnit: RetryUnit;
    retrySecondsPreview: number | null;
    retryBreakdownPreview: string | null;
    retryIsOutOfRange: boolean;
    setRetryAmount: Dispatch<SetStateAction<string>>;
    setRetryUnit: Dispatch<SetStateAction<RetryUnit>>;
}) {
    return (
        <div className="space-y-2">
            <FieldInfoLabel description="Waktu tunggu yang dikirim sebagai header Retry-After.">Retry Setelah</FieldInfoLabel>
            <div className="grid grid-cols-[minmax(0,1fr)_120px] gap-2">
                <Input
                    type="number"
                    step="1"
                    value={retryAmount}
                    disabled={disabled}
                    onChange={(event) => {
                        const amount = event.target.value;

                        setRetryAmount(amount);
                        form.setData('retry_seconds', retryPartsToSeconds(amount, retryUnit));
                    }}
                />
                <Select
                    value={retryUnit}
                    disabled={disabled}
                    onValueChange={(value) => {
                        const unit = value as RetryUnit;

                        setRetryUnit(unit);
                        form.setData('retry_seconds', retryPartsToSeconds(retryAmount, unit));
                    }}
                >
                    <SelectTrigger>
                        <SelectValue placeholder="Unit" />
                    </SelectTrigger>
                    <SelectContent>
                        {retryUnitOptions.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <p className="text-muted-foreground text-xs leading-relaxed">Contoh: 2 jam akan disimpan sebagai 7200 detik untuk Laravel.</p>
            {retryBreakdownPreview ? (
                <div
                    className={
                        retryIsOutOfRange
                            ? 'border-destructive/40 bg-destructive/10 rounded-md border p-3 text-xs leading-relaxed'
                            : 'bg-muted/50 rounded-md border p-3 text-xs leading-relaxed'
                    }
                >
                    <p className="font-medium">Konversi Retry</p>
                    <p className="text-muted-foreground mt-1">
                        {retryAmount} {retryUnitOptions.find((option) => option.value === retryUnit)?.label.toLowerCase()} = {retrySecondsPreview}{' '}
                        detik
                    </p>
                    <p className="text-muted-foreground mt-1">Dibaca pengguna sebagai sekitar {retryBreakdownPreview}.</p>
                    {retryIsOutOfRange ? (
                        <p className="text-destructive mt-2 font-medium">Nilai retry harus berada di antara 30 detik sampai 30 hari.</p>
                    ) : null}
                </div>
            ) : null}
            <InputError message={form.errors.retry_seconds} />
        </div>
    );
}
