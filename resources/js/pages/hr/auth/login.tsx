import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/react';
import { Building2, LoaderCircle, Network, ShieldCheck, UsersRound } from 'lucide-react';
import { FormEventHandler } from 'react';

interface LoginForm {
    [key: string]: string | boolean;
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function HRLogin({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('hr.login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Masuk HR" />
            <main className="bg-background text-foreground grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)]">
                <section className="relative hidden overflow-hidden bg-emerald-950 text-white lg:block">
                    <div className="absolute inset-0 bg-[linear-gradient(135deg,rgba(16,185,129,0.28),transparent_42%),linear-gradient(to_right,rgba(255,255,255,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.07)_1px,transparent_1px)] bg-[size:auto,42px_42px,42px_42px]" />
                    <div className="relative flex h-full flex-col p-10">
                        <Link href={route('home')} className="flex w-fit items-center gap-3">
                            <span className="flex size-11 items-center justify-center rounded-lg bg-white/12">
                                <Building2 className="size-6" />
                            </span>
                            <div>
                                <p className="font-semibold">HR Workspace</p>
                                <p className="text-xs text-emerald-100/80">People operation gateway</p>
                            </div>
                        </Link>

                        <div className="mt-auto max-w-xl space-y-6">
                            <Badge className="border-white/20 bg-white/10 text-white hover:bg-white/10">Project HR</Badge>
                            <div className="space-y-4">
                                <h1 className="text-5xl leading-tight font-semibold">Masuk ke ruang kerja HR.</h1>
                                <p className="text-base leading-7 text-emerald-50/78">
                                    Kelola departement, struktur organisasi, employee profile, dokumen, dan lifecycle karyawan dari area HR yang
                                    terpisah.
                                </p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-3">
                                {[
                                    ['Departements', UsersRound],
                                    ['Policy Access', ShieldCheck],
                                    ['Integration Ready', Network],
                                ].map(([label, Icon]) => (
                                    <div key={String(label)} className="rounded-lg border border-white/15 bg-white/8 p-4">
                                        <Icon className="size-5 text-emerald-200" />
                                        <p className="mt-3 text-sm font-medium">{String(label)}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="flex items-center justify-center px-6 py-10">
                    <div className="w-full max-w-sm space-y-7">
                        <div className="space-y-3 text-center">
                            <Link
                                href={route('home')}
                                className="mx-auto flex size-12 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 lg:hidden"
                            >
                                <Building2 className="size-6" />
                            </Link>
                            <div>
                                <h2 className="text-2xl font-semibold">Masuk HR</h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">Gunakan akun HR untuk masuk ke dashboard project HR.</p>
                            </div>
                        </div>

                        <form className="space-y-5" onSubmit={submit}>
                            <div className="space-y-2">
                                <Label htmlFor="email">Email address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autoFocus
                                    autoComplete="email"
                                    value={data.email}
                                    onChange={(event) => setData('email', event.target.value)}
                                    placeholder="hr.manager@mail.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">Password</Label>
                                    {canResetPassword && (
                                        <TextLink href={route('password.request')} className="ml-auto text-sm">
                                            Forgot password?
                                        </TextLink>
                                    )}
                                </div>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    placeholder="Password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <label className="flex items-center gap-3 text-sm">
                                <Checkbox checked={data.remember} onCheckedChange={(checked) => setData('remember', checked === true)} />
                                Remember me
                            </label>

                            <Button type="submit" className="w-full bg-emerald-600 text-white hover:bg-emerald-700" disabled={processing}>
                                {processing && <LoaderCircle className="size-4 animate-spin" />}
                                Masuk HR
                            </Button>
                        </form>

                        {status && <div className="text-center text-sm font-medium text-emerald-600">{status}</div>}
                    </div>
                </section>
            </main>
        </>
    );
}
