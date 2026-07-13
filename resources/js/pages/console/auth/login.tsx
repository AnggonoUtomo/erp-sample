import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, useForm } from '@inertiajs/react';
import { LayoutDashboard, LoaderCircle, Settings2, ShieldCheck, UsersRound } from 'lucide-react';
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

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Masuk Console" />
            <main className="bg-background text-foreground grid min-h-screen lg:grid-cols-[minmax(0,1.05fr)_minmax(420px,0.95fr)]">
                <section className="relative hidden overflow-hidden bg-sky-950 text-white lg:block">
                    <div className="absolute inset-0 bg-[linear-gradient(135deg,rgba(14,165,233,0.3),transparent_42%),linear-gradient(to_right,rgba(255,255,255,0.08)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.07)_1px,transparent_1px)] bg-[size:auto,42px_42px,42px_42px]" />
                    <div className="relative flex h-full flex-col p-10">
                        <Link href={route('home')} className="flex w-fit items-center gap-3">
                            <span className="flex size-11 items-center justify-center rounded-lg bg-white/12">
                                <LayoutDashboard className="size-6" />
                            </span>
                            <div>
                                <p className="font-semibold">Console Workspace</p>
                                <p className="text-xs text-sky-100/80">System administration gateway</p>
                            </div>
                        </Link>

                        <div className="mt-auto max-w-xl space-y-6">
                            <Badge className="border-white/20 bg-white/10 text-white hover:bg-white/10">Project Console</Badge>
                            <div className="space-y-4">
                                <h1 className="text-5xl leading-tight font-semibold">Masuk ke pusat kendali sistem.</h1>
                                <p className="text-base leading-7 text-sky-50/78">
                                    Kelola pengguna, hak akses, konfigurasi, audit, dan operasional aplikasi dari workspace Console.
                                </p>
                            </div>
                            <div className="grid gap-3 sm:grid-cols-3">
                                {[
                                    ['User Management', UsersRound],
                                    ['Policy Access', ShieldCheck],
                                    ['System Settings', Settings2],
                                ].map(([label, Icon]) => (
                                    <div key={String(label)} className="rounded-lg border border-white/15 bg-white/8 p-4">
                                        <Icon className="size-5 text-sky-200" />
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
                                className="mx-auto flex size-12 items-center justify-center rounded-lg bg-sky-500/10 text-sky-600 lg:hidden"
                            >
                                <LayoutDashboard className="size-6" />
                            </Link>
                            <div>
                                <h2 className="text-2xl font-semibold">Masuk Console</h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">
                                    Gunakan akun administrator untuk masuk ke workspace Console.
                                </p>
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
                                    placeholder="admin@mail.com"
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

                            <Button type="submit" className="w-full bg-sky-600 text-white hover:bg-sky-700" disabled={processing}>
                                {processing && <LoaderCircle className="size-4 animate-spin" />}
                                Masuk Console
                            </Button>
                        </form>

                        {status && <div className="text-center text-sm font-medium text-sky-600">{status}</div>}
                    </div>
                </section>
            </main>
        </>
    );
}
