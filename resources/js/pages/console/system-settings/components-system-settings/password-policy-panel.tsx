import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { KeyRound, Save, Send } from 'lucide-react';
import type { FormEvent } from 'react';
import type { PasswordPolicy, PasswordPolicyForm } from '../types';

type Props = {
    can: { update: boolean };
    passwordPolicy: PasswordPolicy;
    form: InertiaFormProps<PasswordPolicyForm>;
    submit: (event: FormEvent) => void;
};

const passwordToggles: { key: keyof PasswordPolicyForm; label: string }[] = [
    { key: 'require_uppercase', label: 'Wajib huruf besar' },
    { key: 'require_lowercase', label: 'Wajib huruf kecil' },
    { key: 'require_numbers', label: 'Wajib angka' },
    { key: 'require_symbols', label: 'Wajib simbol' },
    { key: 'uncompromised', label: 'Cek password bocor' },
];

export function PasswordPolicyPanel({ can, passwordPolicy, form, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-indigo flex size-10 items-center justify-center rounded-md">
                        <KeyRound className="size-5" />
                    </span>
                    Password Policy
                </CardTitle>
                <CardDescription>Atur kekuatan password untuk reset password dan perubahan password pemilik akun.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Minimum Length" value={passwordPolicy.min_length} />
                        <SummaryTile label="Expiry" value={passwordPolicy.expiry_days || 'Off'} />
                        <SummaryTile label="History" value={passwordPolicy.history_count || 'Off'} />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <NumberField
                            label="Minimum Length"
                            description="Panjang minimal password baru."
                            min={8}
                            max={128}
                            value={form.data.min_length}
                            disabled={!can.update || form.processing}
                            error={form.errors.min_length}
                            onChange={(value) => form.setData('min_length', value)}
                        />
                        <NumberField
                            label="Password Expiry (hari)"
                            description="Jumlah hari sebelum user perlu mengganti password. Nilai 0 berarti nonaktif."
                            min={0}
                            max={365}
                            value={form.data.expiry_days}
                            disabled={!can.update || form.processing}
                            error={form.errors.expiry_days}
                            onChange={(value) => form.setData('expiry_days', value)}
                        />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        {passwordToggles.map((item) => (
                            <label key={item.key} className="bg-background/60 flex items-center gap-3 rounded-lg border p-4">
                                <Checkbox
                                    checked={Boolean(form.data[item.key])}
                                    disabled={!can.update || form.processing}
                                    onCheckedChange={(checked) => form.setData(item.key, Boolean(checked) as never)}
                                />
                                <span className="text-sm font-medium">{item.label}</span>
                            </label>
                        ))}
                        <NumberField
                            label="Password History"
                            description="Jumlah password lama yang tidak boleh dipakai ulang. Nilai 0 berarti nonaktif."
                            min={0}
                            max={24}
                            value={form.data.history_count}
                            disabled={!can.update || form.processing}
                            error={form.errors.history_count}
                            onChange={(value) => form.setData('history_count', value)}
                            required={false}
                        />
                    </div>

                    <div className="rounded-lg border border-dashed p-4">
                        <p className="text-sm font-medium">Status Implementasi</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                            Aturan minimum length, uppercase/lowercase, angka, simbol, dan uncompromised sudah aktif pada reset password dan
                            profile password. Expiry/history disimpan untuk tahap enforcement berikutnya.
                        </p>
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
                                    Simpan Password
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
            <p className="mt-2 text-2xl font-semibold">{value}</p>
        </div>
    );
}

function NumberField({
    label,
    description,
    min,
    max,
    value,
    disabled,
    error,
    onChange,
    required = true,
}: {
    label: string;
    description: string;
    min: number;
    max: number;
    value: string;
    disabled: boolean;
    error?: string;
    onChange: (value: string) => void;
    required?: boolean;
}) {
    return (
        <div className="space-y-2">
            <FieldInfoLabel required={required} description={description}>
                {label}
            </FieldInfoLabel>
            <Input type="number" min={min} max={max} value={value} disabled={disabled} onChange={(event) => onChange(event.target.value)} />
            <InputError message={error} />
        </div>
    );
}
