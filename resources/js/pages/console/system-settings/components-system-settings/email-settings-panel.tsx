import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { CheckCircle2, Mail, MailCheck, Save, Send, ServerCog, ShieldCheck } from 'lucide-react';
import type { FormEvent } from 'react';
import type { EmailSettingForm, EmailSettings, TestEmailForm } from '../types';
import { boolLabel } from '../utils';

type Props = {
    can: { update: boolean };
    emailSettings: EmailSettings;
    form: InertiaFormProps<EmailSettingForm>;
    testForm: InertiaFormProps<TestEmailForm>;
    mailerUsesSmtp: boolean;
    submit: (event: FormEvent) => void;
    submitTestEmail: (event: FormEvent) => void;
};

export function EmailSettingsPanel({ can, emailSettings, form, testForm, mailerUsesSmtp, submit, submitTestEmail }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-indigo flex size-10 items-center justify-center rounded-md">
                        <ServerCog className="size-5" />
                    </span>
                    Konfigurasi SMTP
                </CardTitle>
                <CardDescription>Simpan pengaturan email yang akan dipakai runtime aplikasi.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <div className="mb-8 grid gap-4 lg:grid-cols-2">
                    <div className="bg-background/60 rounded-lg border p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-sky flex size-9 shrink-0 items-center justify-center rounded-md">
                                <Mail className="size-4" />
                            </span>
                            <div className="min-w-0 flex-1 space-y-3">
                                <div>
                                    <p className="text-sm font-medium">Status Email</p>
                                    <p className="text-muted-foreground mt-1 text-xs">Kondisi konfigurasi pengiriman email.</p>
                                </div>
                                <div className="grid gap-2 text-sm">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-muted-foreground">Pengiriman</span>
                                        <Badge variant={emailSettings.enabled ? 'default' : 'secondary'}>{boolLabel(emailSettings.enabled)}</Badge>
                                    </div>
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-muted-foreground">Mailer</span>
                                        <span className="font-medium uppercase">{emailSettings.mailer}</span>
                                    </div>
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-muted-foreground">Password SMTP</span>
                                        <span className="flex items-center gap-1.5 font-medium">
                                            {emailSettings.password_configured && <CheckCircle2 className="size-4 text-emerald-500" />}
                                            {emailSettings.password_configured ? 'Tersimpan' : 'Belum ada'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-background/60 rounded-lg border p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-emerald flex size-9 shrink-0 items-center justify-center rounded-md">
                                <ShieldCheck className="size-4" />
                            </span>
                            <div className="min-w-0 flex-1 space-y-3">
                                <div>
                                    <p className="text-sm font-medium">Otomasi Aktivasi</p>
                                    <p className="text-muted-foreground mt-1 text-xs">
                                        Dipakai saat user baru dibuat atau admin mengirim tautan atur password.
                                    </p>
                                </div>
                                <div className="grid gap-2 text-sm">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-muted-foreground">User baru</span>
                                        <Badge variant="secondary">{boolLabel(emailSettings.send_credentials_on_create)}</Badge>
                                    </div>
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="text-muted-foreground">Link reset</span>
                                        <Badge variant="secondary">{boolLabel(emailSettings.send_credentials_on_password_update)}</Badge>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form onSubmit={submitTestEmail} className="bg-background/60 mb-8 rounded-lg border p-4">
                    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
                        <div className="space-y-2">
                            <FieldInfoLabel
                                htmlFor="test_recipient"
                                required
                                description="Email tujuan untuk memastikan konfigurasi mailer yang tersimpan dapat mengirim pesan."
                            >
                                Test Email SMTP
                            </FieldInfoLabel>
                            <Input
                                id="test_recipient"
                                type="email"
                                value={testForm.data.recipient}
                                disabled={!can.update || testForm.processing}
                                placeholder="admin@example.com"
                                onChange={(event) => testForm.setData('recipient', event.target.value)}
                            />
                            <InputError message={testForm.errors.recipient} />
                        </div>
                        <Button type="submit" variant="outline" disabled={!can.update || testForm.processing} className="h-11 min-w-36">
                            {testForm.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Mengirim...
                                </>
                            ) : (
                                <>
                                    <MailCheck className="size-4" />
                                    Kirim Test
                                </>
                            )}
                        </Button>
                    </div>
                    <p className="text-muted-foreground mt-3 text-xs leading-relaxed">
                        Test memakai konfigurasi email yang sudah tersimpan. Simpan perubahan SMTP terlebih dahulu sebelum menekan tombol ini.
                    </p>
                </form>

                <form onSubmit={submit} className="space-y-8">
                    <section className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                                <Checkbox checked={form.data.enabled} onCheckedChange={(checked) => form.setData('enabled', Boolean(checked))} />
                                <span>
                                    <span className="block text-sm font-medium">Aktifkan pengiriman email</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">
                                        Jika nonaktif, tautan aktivasi dan tautan atur password tidak akan dikirim.
                                    </span>
                                </span>
                            </label>

                            <div className="space-y-2">
                                <FieldInfoLabel required description="Gunakan SMTP untuk email sungguhan, log/array untuk development.">
                                    Mailer
                                </FieldInfoLabel>
                                <Select
                                    value={form.data.mailer}
                                    onValueChange={(value: EmailSettingForm['mailer']) => form.setData('mailer', value)}
                                    disabled={!can.update}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih mailer" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="smtp">SMTP</SelectItem>
                                        <SelectItem value="log">Log</SelectItem>
                                        <SelectItem value="array">Array</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={form.errors.mailer} />
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <FieldInfoLabel
                                    htmlFor="host"
                                    required={mailerUsesSmtp}
                                    description="Alamat SMTP server, misalnya smtp.gmail.com atau smtp.mailtrap.io."
                                >
                                    SMTP Host
                                </FieldInfoLabel>
                                <Input
                                    id="host"
                                    value={form.data.host}
                                    disabled={!can.update || !mailerUsesSmtp}
                                    placeholder="smtp.example.com"
                                    onChange={(event) => form.setData('host', event.target.value)}
                                />
                                <InputError message={form.errors.host} />
                            </div>

                            <div className="space-y-2">
                                <FieldInfoLabel htmlFor="port" required={mailerUsesSmtp} description="Port umum SMTP adalah 587, 465, atau 2525.">
                                    SMTP Port
                                </FieldInfoLabel>
                                <Input
                                    id="port"
                                    type="number"
                                    min="1"
                                    max="65535"
                                    value={form.data.port}
                                    disabled={!can.update || !mailerUsesSmtp}
                                    placeholder="587"
                                    onChange={(event) => form.setData('port', event.target.value)}
                                />
                                <InputError message={form.errors.port} />
                            </div>

                            <div className="space-y-2">
                                <FieldInfoLabel htmlFor="username" description="Username SMTP, biasanya sama dengan email provider.">
                                    SMTP Username
                                </FieldInfoLabel>
                                <Input
                                    id="username"
                                    value={form.data.username}
                                    disabled={!can.update || !mailerUsesSmtp}
                                    placeholder="apikey atau email"
                                    onChange={(event) => form.setData('username', event.target.value)}
                                />
                                <InputError message={form.errors.username} />
                            </div>

                            <div className="space-y-2">
                                <FieldInfoLabel
                                    htmlFor="password"
                                    description="Kosongkan jika tidak ingin mengubah password SMTP yang sudah tersimpan."
                                >
                                    SMTP Password
                                </FieldInfoLabel>
                                <Input
                                    id="password"
                                    type="password"
                                    value={form.data.password}
                                    disabled={!can.update || !mailerUsesSmtp}
                                    placeholder={emailSettings.password_configured ? 'Password sudah tersimpan' : 'Masukkan password SMTP'}
                                    onChange={(event) => form.setData('password', event.target.value)}
                                />
                                <InputError message={form.errors.password} />
                            </div>

                            <div className="space-y-2">
                                <FieldInfoLabel description="Pilih TLS/SSL sesuai provider SMTP.">Encryption</FieldInfoLabel>
                                <Select
                                    value={form.data.encryption}
                                    onValueChange={(value: EmailSettingForm['encryption']) => form.setData('encryption', value)}
                                    disabled={!can.update || !mailerUsesSmtp}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Pilih encryption" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="tls">TLS</SelectItem>
                                        <SelectItem value="ssl">SSL</SelectItem>
                                        <SelectItem value="none">None</SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError message={form.errors.encryption} />
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <div className="space-y-2">
                                <FieldInfoLabel htmlFor="from_address" required description="Alamat email pengirim yang terlihat oleh penerima.">
                                    From Address
                                </FieldInfoLabel>
                                <Input
                                    id="from_address"
                                    type="email"
                                    value={form.data.from_address}
                                    disabled={!can.update}
                                    placeholder="noreply@example.com"
                                    onChange={(event) => form.setData('from_address', event.target.value)}
                                />
                                <InputError message={form.errors.from_address} />
                            </div>

                            <div className="space-y-2">
                                <FieldInfoLabel htmlFor="from_name" required description="Nama pengirim yang tampil di inbox penerima.">
                                    From Name
                                </FieldInfoLabel>
                                <Input
                                    id="from_name"
                                    value={form.data.from_name}
                                    disabled={!can.update}
                                    placeholder="Urbanclap Admin"
                                    onChange={(event) => form.setData('from_name', event.target.value)}
                                />
                                <InputError message={form.errors.from_name} />
                            </div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                                <Checkbox
                                    checked={form.data.send_credentials_on_create}
                                    disabled={!can.update}
                                    onCheckedChange={(checked) => form.setData('send_credentials_on_create', Boolean(checked))}
                                />
                                <span>
                                    <span className="block text-sm font-medium">Kirim tautan aktivasi saat user dibuat</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">
                                        User menerima email berisi tautan untuk aktivasi akun dan mengatur password melalui queue mail.
                                    </span>
                                </span>
                            </label>

                            <label className="bg-background/60 flex items-start gap-3 rounded-lg border p-4">
                                <Checkbox
                                    checked={form.data.send_credentials_on_password_update}
                                    disabled={!can.update}
                                    onCheckedChange={(checked) => form.setData('send_credentials_on_password_update', Boolean(checked))}
                                />
                                <span>
                                    <span className="block text-sm font-medium">Izinkan tautan atur password dari edit user</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-relaxed">
                                        Email diproses via queue mail saat admin mencentang kirim tautan atur password pada edit user.
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div className="space-y-2">
                            <FieldInfoLabel htmlFor="credential_subject" description="Subject email aktivasi yang dikirim ke user.">
                                Subject Email Aktivasi
                            </FieldInfoLabel>
                            <Input
                                id="credential_subject"
                                value={form.data.credential_subject}
                                disabled={!can.update}
                                placeholder="Aktivasi akun dan atur password"
                                onChange={(event) => form.setData('credential_subject', event.target.value)}
                            />
                            <InputError message={form.errors.credential_subject} />
                        </div>

                        <div className="space-y-2">
                            <FieldInfoLabel htmlFor="credential_intro" description="Kalimat pembuka sebelum tombol aktivasi dan atur password.">
                                Intro Email Aktivasi
                            </FieldInfoLabel>
                            <textarea
                                id="credential_intro"
                                value={form.data.credential_intro}
                                disabled={!can.update}
                                rows={4}
                                className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus:ring-ring min-h-24 w-full rounded-md border px-3 py-2 text-sm focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
                                placeholder="Akun kamu sudah dibuat. Gunakan tautan berikut untuk aktivasi dan mengatur password."
                                onChange={(event) => form.setData('credential_intro', event.target.value)}
                            />
                            <InputError message={form.errors.credential_intro} />
                        </div>
                    </section>

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={!can.update || form.processing} className="h-11 min-w-36">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Setting
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
