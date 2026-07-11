import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Save, Send, ShieldAlert } from 'lucide-react';
import type { FormEvent } from 'react';
import type { SecurityPolicy, SecurityPolicyForm } from '../types';

type Props = {
    can: { update: boolean };
    securityPolicy: SecurityPolicy;
    form: InertiaFormProps<SecurityPolicyForm>;
    submit: (event: FormEvent) => void;
};

const securityToggles: { key: keyof SecurityPolicyForm; label: string; description: string }[] = [
    {
        key: 'require_email_verification',
        label: 'Wajib verifikasi email',
        description: 'User harus verifikasi email sebelum akses penuh.',
    },
    {
        key: 'audit_sensitive_actions',
        label: 'Audit aksi sensitif',
        description: 'Aksi penting tetap masuk ke audit log.',
    },
    {
        key: 'single_session_per_user',
        label: 'Single session per user',
        description: 'Disiapkan untuk membatasi satu sesi aktif per user.',
    },
];

export function SecurityPolicyPanel({ can, securityPolicy, form, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-rose flex size-10 items-center justify-center rounded-md">
                        <ShieldAlert className="size-5" />
                    </span>
                    Security Policy
                </CardTitle>
                <CardDescription>Atur kebijakan keamanan aplikasi, session, dan login throttling.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Session Timeout" value={`${securityPolicy.session_lifetime_minutes}m`} />
                        <SummaryTile label="Login Attempts" value={securityPolicy.login_max_attempts} />
                        <SummaryTile label="Password Confirm" value={`${Math.round(securityPolicy.password_confirmation_timeout_seconds / 60)}m`} />
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        {securityToggles.map((item) => (
                            <label key={item.key} className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                                <Checkbox
                                    checked={Boolean(form.data[item.key])}
                                    disabled={!can.update || form.processing}
                                    onCheckedChange={(checked) => form.setData(item.key, Boolean(checked) as never)}
                                />
                                <span>
                                    <span className="block text-sm font-medium">{item.label}</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">{item.description}</span>
                                </span>
                            </label>
                        ))}
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <NumberField
                            label="Session Lifetime (menit)"
                            description="Durasi idle sebelum session dianggap habis."
                            min={5}
                            max={1440}
                            value={form.data.session_lifetime_minutes}
                            disabled={!can.update || form.processing}
                            error={form.errors.session_lifetime_minutes}
                            onChange={(value) => form.setData('session_lifetime_minutes', value)}
                        />
                        <NumberField
                            label="Login Max Attempts"
                            description="Jumlah maksimal login gagal sebelum terkena throttle."
                            min={3}
                            max={20}
                            value={form.data.login_max_attempts}
                            disabled={!can.update || form.processing}
                            error={form.errors.login_max_attempts}
                            onChange={(value) => form.setData('login_max_attempts', value)}
                        />
                        <NumberField
                            label="Login Decay (menit)"
                            description="Durasi throttle setelah login gagal berulang."
                            min={1}
                            max={120}
                            value={form.data.login_decay_minutes}
                            disabled={!can.update || form.processing}
                            error={form.errors.login_decay_minutes}
                            onChange={(value) => form.setData('login_decay_minutes', value)}
                        />
                        <NumberField
                            label="Password Confirmation Timeout (detik)"
                            description="Batas waktu validasi ulang password untuk area sensitif."
                            min={60}
                            max={10800}
                            value={form.data.password_confirmation_timeout_seconds}
                            disabled={!can.update || form.processing}
                            error={form.errors.password_confirmation_timeout_seconds}
                            onChange={(value) => form.setData('password_confirmation_timeout_seconds', value)}
                        />
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
                                    Simpan Security
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
}: {
    label: string;
    description: string;
    min: number;
    max: number;
    value: string;
    disabled: boolean;
    error?: string;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <FieldInfoLabel required description={description}>
                {label}
            </FieldInfoLabel>
            <Input type="number" min={min} max={max} value={value} disabled={disabled} onChange={(event) => onChange(event.target.value)} />
            <InputError message={error} />
        </div>
    );
}
