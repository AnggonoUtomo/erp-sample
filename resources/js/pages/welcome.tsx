import AppLogoIcon from '@/components/app-logo-icon';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    Building2,
    Cable,
    CheckCircle2,
    CircleDot,
    LayoutDashboard,
    LockKeyhole,
    Network,
    ShieldCheck,
    Sparkles,
    TerminalSquare,
    WandSparkles,
} from 'lucide-react';

type ProjectItem = {
    name: string;
    description: string;
    status: 'ready' | 'planned';
    href: string | null;
    loginHref?: string;
    icon: typeof LayoutDashboard;
    tone: string;
    modules: string[];
    meta: string;
};

const projects: ProjectItem[] = [
    {
        name: 'Console',
        description: 'Control center untuk user, access control, system setting, audit, queue, scheduler, backup, dan monitoring.',
        status: 'ready',
        href: route('dashboard'),
        loginHref: route('login'),
        icon: LayoutDashboard,
        tone: 'icon-tone-sky',
        modules: ['User Management', 'Access Control', 'System Settings', 'Monitoring'],
        meta: '10 modules ready',
    },
    {
        name: 'HR',
        description: 'Workspace people operation untuk struktur organisasi, departement, employee profile, lifecycle, dan fondasi Attendance/Payroll.',
        status: 'ready',
        href: route('hr.dashboard'),
        loginHref: route('hr.login'),
        icon: Building2,
        tone: 'icon-tone-emerald',
        modules: ['departements', 'Positions', 'Employees', 'Organization'],
        meta: 'Departements ready',
    },
];

const foundations = [
    { label: 'Shared Kernel', icon: Network },
    { label: 'Module Contract', icon: Cable },
    { label: 'Domain Event', icon: CircleDot },
];

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;
    const isAuthenticated = Boolean(auth.user);

    return (
        <>
            <Head title="Project Launcher" />
            <main className="bg-background text-foreground min-h-screen overflow-hidden">
                <div className="absolute inset-0 -z-10 bg-[linear-gradient(to_right,color-mix(in_oklab,var(--border)_52%,transparent)_1px,transparent_1px),linear-gradient(to_bottom,color-mix(in_oklab,var(--border)_46%,transparent)_1px,transparent_1px)] bg-[size:44px_44px]" />
                <div className="absolute inset-x-0 top-0 -z-10 h-80 bg-[linear-gradient(180deg,color-mix(in_oklab,var(--primary)_12%,transparent),transparent)]" />

                <div className="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-5 py-6 sm:px-8 lg:px-10">
                    <header className="bg-card/86 supports-[backdrop-filter]:bg-card/72 flex items-center justify-between gap-4 rounded-lg border px-4 py-3 shadow-sm backdrop-blur">
                        <div className="flex items-center gap-3">
                            <span className="bg-primary text-primary-foreground flex size-10 items-center justify-center rounded-md">
                                <AppLogoIcon className="size-6 fill-current" />
                            </span>
                            <div>
                                <p className="text-sm font-semibold">Console Starterkit</p>
                                <p className="text-muted-foreground text-xs">Multi-project launcher</p>
                            </div>
                        </div>

                        <div className="hidden items-center gap-2 md:flex">
                            {foundations.map((item) => {
                                const Icon = item.icon;

                                return (
                                    <span key={item.label} className="text-muted-foreground bg-muted/70 inline-flex items-center gap-2 rounded-md px-3 py-2 text-xs font-medium">
                                        <Icon className="size-3.5" />
                                        {item.label}
                                    </span>
                                );
                            })}
                        </div>

                        {isAuthenticated ? (
                            <Button asChild>
                                <Link href={route('dashboard')}>
                                    Buka Console
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                        ) : null}
                    </header>

                    <section className="grid flex-1 items-center gap-8 py-10 lg:grid-cols-[minmax(0,0.95fr)_minmax(520px,1.05fr)] lg:py-14">
                        <div className="space-y-7">
                            <div className="space-y-5">
                                <Badge variant="outline" className="bg-card/80 w-fit gap-2 px-3 py-1.5">
                                    <Sparkles className="size-3.5" />
                                    Console as project foundation
                                </Badge>
                                <div className="max-w-3xl space-y-4">
                                    <h1 className="text-4xl leading-tight font-semibold tracking-tight sm:text-5xl lg:text-6xl">
                                        Satu pintu untuk semua workspace project.
                                    </h1>
                                    <p className="text-muted-foreground max-w-2xl text-base leading-7 sm:text-lg">
                                        Console menjadi lapisan awal untuk administrasi, konfigurasi, keamanan, monitoring, dan generator modul lintas project.
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                <Metric label="Project aktif" value="Console + HR" />
                                <Metric label="HR module" value="1" />
                                <Metric label="Next phase" value="HR Core" />
                            </div>

                            <div className="grid gap-3 sm:grid-cols-3">
                                {foundations.map((item) => {
                                    const Icon = item.icon;

                                    return (
                                        <div key={item.label} className="bg-card/82 rounded-lg border p-4 shadow-sm">
                                            <span className="dashboard-icon icon-tone-indigo flex size-9 items-center justify-center rounded-md">
                                                <Icon className="size-4" />
                                            </span>
                                            <p className="mt-3 text-sm font-semibold">{item.label}</p>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <ConsolePreview isAuthenticated={isAuthenticated} />
                    </section>

                    <section className="pb-8">
                        <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 className="text-xl font-semibold">Project Workspace</h2>
                                <p className="text-muted-foreground text-sm">Pilih project yang sudah aktif atau siapkan target generator berikutnya.</p>
                            </div>
                            <Badge variant="secondary" className="w-fit">Console ready</Badge>
                        </div>

                        <div className="grid gap-4 lg:grid-cols-2">
                            {projects.map((project) => (
                                <ProjectCard key={project.name} project={project} isAuthenticated={isAuthenticated} />
                            ))}
                        </div>
                    </section>
                </div>
            </main>
        </>
    );
}

function ConsolePreview({ isAuthenticated }: { isAuthenticated: boolean }) {
    return (
        <div className="relative">
            <div className="bg-card/90 overflow-hidden rounded-lg border shadow-xl shadow-black/5">
                <div className="border-b px-4 py-3">
                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-2">
                            <span className="size-2.5 rounded-full bg-rose-400" />
                            <span className="size-2.5 rounded-full bg-amber-400" />
                            <span className="size-2.5 rounded-full bg-emerald-400" />
                        </div>
                        <Badge variant={isAuthenticated ? 'default' : 'secondary'}>{isAuthenticated ? 'Session aktif' : 'Console login'}</Badge>
                    </div>
                </div>

                <div className="grid min-h-[420px] md:grid-cols-[180px_minmax(0,1fr)]">
                    <aside className="bg-muted/40 hidden border-r p-4 md:block">
                        <div className="mb-5 flex items-center gap-2">
                            <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-md">
                                <TerminalSquare className="size-4" />
                            </span>
                            <span className="text-sm font-semibold">Console</span>
                        </div>
                        <div className="space-y-2">
                            {['Dashboard', 'Users', 'Access Control', 'System Settings', 'Monitors'].map((item, index) => (
                                <div
                                    key={item}
                                    className={
                                        index === 0
                                            ? 'bg-background text-foreground rounded-md border px-3 py-2 text-xs font-medium shadow-xs'
                                            : 'text-muted-foreground rounded-md px-3 py-2 text-xs font-medium'
                                    }
                                >
                                    {item}
                                </div>
                            ))}
                        </div>
                    </aside>

                    <div className="space-y-4 p-4 sm:p-5">
                        <div className="grid gap-3 sm:grid-cols-3">
                            <PreviewStat label="Modules" value="10" tone="icon-tone-sky" />
                            <PreviewStat label="Policies" value="Ready" tone="icon-tone-emerald" />
                            <PreviewStat label="Events" value="Next" tone="icon-tone-amber" />
                        </div>

                        <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_180px]">
                            <div className="rounded-lg border p-4">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <p className="text-sm font-semibold">Module Contract</p>
                                        <p className="text-muted-foreground text-xs">routes, permissions, provider, navigation</p>
                                    </div>
                                    <ShieldCheck className="text-primary size-5" />
                                </div>
                                <div className="space-y-3">
                                    {[
                                        ['Console', '10 modules', '100%'],
                                        ['HR', '1 module', '30%'],
                                        ['Next project', 'planned', '10%'],
                                    ].map(([name, meta, width]) => (
                                        <div key={name} className="space-y-2">
                                            <div className="flex items-center justify-between text-xs">
                                                <span className="font-medium">{name}</span>
                                                <span className="text-muted-foreground">{meta}</span>
                                            </div>
                                            <div className="bg-muted h-2 overflow-hidden rounded-full">
                                                <div className="bg-primary h-full rounded-full" style={{ width }} />
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            <div className="rounded-lg border p-4">
                                <p className="text-sm font-semibold">Generator Queue</p>
                                <div className="mt-4 space-y-3">
                                    {['Project', 'Module', 'Event'].map((item, index) => (
                                        <div key={item} className="flex items-center gap-2 text-xs">
                                            <span className={index === 0 ? 'text-primary' : 'text-muted-foreground'}>
                                                {index === 0 ? <WandSparkles className="size-4" /> : <CheckCircle2 className="size-4" />}
                                            </span>
                                            <span>{item}</span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg border p-4">
                            <div className="grid gap-3 sm:grid-cols-3">
                                {['Shared Kernel', 'Module Contract', 'Domain Event'].map((item) => (
                                    <div key={item} className="bg-muted/45 rounded-md px-3 py-2 text-xs font-medium">
                                        {item}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function ProjectCard({ project, isAuthenticated }: { project: ProjectItem; isAuthenticated: boolean }) {
    const Icon = project.icon;
    const href = project.status === 'ready' ? (isAuthenticated ? project.href : (project.loginHref ?? route('login'))) : null;

    const content = (
        <div className="group bg-card/92 text-card-foreground flex h-full flex-col rounded-lg border p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-primary/35 hover:shadow-lg">
            <div className="mb-5 flex items-start justify-between gap-4">
                <span className={`dashboard-icon ${project.tone} flex size-12 items-center justify-center rounded-md`}>
                    <Icon className="size-6" />
                </span>
                <Badge variant={project.status === 'ready' ? 'default' : 'secondary'}>{project.status === 'ready' ? 'Ready' : 'Planned'}</Badge>
            </div>

            <div className="min-w-0 flex-1 space-y-3">
                <div>
                    <h3 className="text-xl font-semibold">{project.name}</h3>
                    <p className="text-muted-foreground mt-1 text-xs font-medium">{project.meta}</p>
                </div>
                <p className="text-muted-foreground text-sm leading-6">{project.description}</p>
                <div className="flex flex-wrap gap-2">
                    {project.modules.map((module) => (
                        <span key={module} className="bg-muted text-muted-foreground rounded-md px-2.5 py-1 text-xs font-medium">
                            {module}
                        </span>
                    ))}
                </div>
            </div>

            <div className="mt-5 flex items-center justify-between border-t pt-4">
                {project.status === 'ready' ? (
                    <span className="text-primary inline-flex items-center gap-2 text-sm font-semibold">
                        {isAuthenticated ? 'Masuk' : 'Masuk Console'}
                        <ArrowRight className="size-4 transition group-hover:translate-x-1" />
                    </span>
                ) : (
                    <span className="text-muted-foreground inline-flex items-center gap-2 text-sm font-semibold">
                        <LockKeyhole className="size-4" />
                        Segera
                    </span>
                )}
                <span className="text-muted-foreground text-xs">workspace</span>
            </div>
        </div>
    );

    return href ? <Link href={href}>{content}</Link> : content;
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="bg-card/88 rounded-lg border p-4 shadow-sm">
            <p className="text-muted-foreground text-xs">{label}</p>
            <p className="mt-2 text-xl font-semibold">{value}</p>
        </div>
    );
}

function PreviewStat({ label, value, tone }: { label: string; value: string; tone: string }) {
    return (
        <div className="rounded-lg border p-3">
            <div className="flex items-center gap-3">
                <span className={`dashboard-icon ${tone} flex size-9 items-center justify-center rounded-md`}>
                    <CircleDot className="size-4" />
                </span>
                <div>
                    <p className="text-muted-foreground text-xs">{label}</p>
                    <p className="text-sm font-semibold">{value}</p>
                </div>
            </div>
        </div>
    );
}
