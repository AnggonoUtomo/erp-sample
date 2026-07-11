import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    ArchiveRestore,
    Boxes,
    CheckCircle2,
    Clock3,
    DatabaseBackup,
    FileClock,
    GitBranch,
    HeartPulse,
    KeyRound,
    Layers3,
    MailCheck,
    Route,
    ScrollText,
    ServerCog,
    Settings2,
    ShieldCheck,
    Sparkles,
    Users,
    Workflow,
} from 'lucide-react';
import { type ComponentType } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const foundationItems = [
    {
        title: 'Module Contract',
        description: 'Manifest module, route, permission, navigation, provider, event, dan integration contract.',
        icon: Boxes,
    },
    {
        title: 'Shared Kernel',
        description: 'Value object, result object, base DTO, dan contract umum lintas project.',
        icon: Layers3,
    },
    {
        title: 'Domain Event',
        description: 'Event standard dengan dispatcher dan listener registry berbasis module manifest.',
        icon: GitBranch,
    },
    {
        title: 'Integration Layer',
        description: 'Adapter, projector, context, dan message DTO untuk komunikasi antar project.',
        icon: Workflow,
    },
];

const quickActions = [
    {
        title: 'Manajemen User',
        href: '/users',
        icon: Users,
        description: 'Kelola user, role assignment, avatar, dan akses console.',
    },
    {
        title: 'Kontrol Akses',
        href: '/access-control',
        icon: ShieldCheck,
        description: 'Kelola role dan permission modular.',
    },
    {
        title: 'System Settings',
        href: '/system-settings',
        icon: Settings2,
        description: 'SMTP, branding, security, maintenance, dan health.',
    },
    {
        title: 'Backup & Restore',
        href: '/backup-restore',
        icon: ArchiveRestore,
        description: 'Backup setting, database, dan restore operasional.',
    },
];

const operationalLinks = [
    { title: 'Audit Logs', href: '/audit-logs', icon: ScrollText },
    { title: 'Login Activity', href: '/login-activities', icon: FileClock },
    { title: 'Queue Monitor', href: '/queue-monitor', icon: ServerCog },
    { title: 'Scheduler Monitor', href: '/scheduler-monitor', icon: Clock3 },
    { title: 'Notification Templates', href: '/notification-templates', icon: MailCheck },
];

const roadmapProjects = ['HR', 'Attendance', 'Payroll', 'Accounting', 'CRM', 'Document Management'];

function IconBox({ icon: Icon, tone = 'primary' }: { icon: ComponentType<{ className?: string }>; tone?: 'primary' | 'success' | 'warning' }) {
    const toneClass =
        tone === 'success'
            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
            : tone === 'warning'
              ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
              : 'bg-primary/10 text-primary';

    return (
        <div className={`flex size-10 shrink-0 items-center justify-center rounded-lg ${toneClass}`}>
            <Icon className="size-5" />
        </div>
    );
}

export default function Dashboard() {
    const { props } = usePage<SharedData>();
    const moduleCount = props.navigation.reduce((total, group) => total + group.items.length, 0);
    const unreadActivities = props.activity_center.unread_count;
    const permissionsCount = Object.keys(props.auth.permissions ?? {}).length;
    const roleCount = Object.keys(props.auth.roles ?? {}).length;

    const summaryCards = [
        {
            title: 'Module Aktif',
            value: String(moduleCount),
            description: 'Menu module yang terdaftar lewat ModuleRegistry.',
            icon: Route,
        },
        {
            title: 'Permission User',
            value: String(permissionsCount),
            description: 'Permission efektif untuk session saat ini.',
            icon: KeyRound,
        },
        {
            title: 'Role User',
            value: String(roleCount),
            description: props.auth.super ? 'Super admin bypass aktif.' : 'Role aktif yang dibagikan ke Inertia.',
            icon: ShieldCheck,
        },
        {
            title: 'Aktivitas Belum Dibaca',
            value: String(unreadActivities),
            description: 'Ringkasan notification center console.',
            icon: Activity,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard Console" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <section className="border-sidebar-border/70 bg-card dark:border-sidebar-border rounded-2xl border p-6 shadow-sm">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div className="max-w-3xl">
                            <Badge variant="secondary" className="rounded-full">
                                <Sparkles className="size-3.5" />
                                Console Starterkit
                            </Badge>
                            <h1 className="mt-4 text-2xl font-semibold tracking-tight sm:text-3xl">Dashboard fondasi project modular.</h1>
                            <p className="text-muted-foreground mt-3 text-sm leading-6">
                                Console ini menjadi pusat administrasi, konfigurasi, observability, dan fondasi arsitektur untuk project bisnis
                                seperti HR, Attendance, Payroll, Accounting, CRM, dan Document Management.
                            </p>
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2 lg:min-w-80">
                            <Button asChild>
                                <Link href="/system-settings">
                                    <Settings2 className="size-4" />
                                    System Settings
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/queue-monitor">
                                    <HeartPulse className="size-4" />
                                    Monitor Runtime
                                </Link>
                            </Button>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map(({ title, value, description, icon }) => (
                        <Card key={title}>
                            <CardContent className="flex gap-4 p-5">
                                <IconBox icon={icon} />
                                <div>
                                    <div className="text-2xl font-semibold">{value}</div>
                                    <div className="mt-1 font-medium">{title}</div>
                                    <p className="text-muted-foreground mt-2 text-sm leading-5">{description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-4 xl:grid-cols-[1.25fr_0.75fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Fondasi Arsitektur</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-2">
                            {foundationItems.map(({ title, description, icon }, index) => (
                                <div key={title} className="bg-background/60 rounded-xl border p-4">
                                    <div className="flex items-start gap-3">
                                        <IconBox icon={icon} tone={index < 2 ? 'primary' : 'success'} />
                                        <div>
                                            <h2 className="font-semibold">{title}</h2>
                                            <p className="text-muted-foreground mt-2 text-sm leading-6">{description}</p>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Status Console</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {[
                                ['Auth Console', 'Login melalui /console/login'],
                                ['Register Default', 'Dinonaktifkan'],
                                ['Permission Seeder', 'Modular'],
                                ['Notification', 'Sonner + Activity Center'],
                                ['Media Library', 'Siap untuk avatar dan dokumen'],
                            ].map(([title, description]) => (
                                <div key={title} className="flex items-start gap-3">
                                    <CheckCircle2 className="mt-0.5 size-5 text-emerald-500" />
                                    <div>
                                        <div className="font-medium">{title}</div>
                                        <div className="text-muted-foreground text-sm">{description}</div>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Aksi Cepat</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2">
                            {quickActions.map(({ title, href, icon, description }) => (
                                <Link
                                    key={title}
                                    href={href}
                                    className="bg-background/60 hover:border-primary/40 hover:bg-primary/5 rounded-xl border p-4 transition"
                                >
                                    <div className="flex items-start gap-3">
                                        <IconBox icon={icon} />
                                        <div>
                                            <div className="font-medium">{title}</div>
                                            <p className="text-muted-foreground mt-1 text-sm leading-5">{description}</p>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Observability & Operasional</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {operationalLinks.map(({ title, href, icon }) => (
                                <Link
                                    key={title}
                                    href={href}
                                    className="bg-background/60 hover:border-primary/40 hover:bg-primary/5 flex items-center justify-between rounded-xl border px-4 py-3 transition"
                                >
                                    <span className="flex items-center gap-3">
                                        <IconBox icon={icon} tone="warning" />
                                        <span className="font-medium">{title}</span>
                                    </span>
                                    <Badge variant="secondary" className="rounded-full">
                                        Buka
                                    </Badge>
                                </Link>
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[1fr_1fr]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Roadmap Project</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-2 sm:grid-cols-2">
                                {roadmapProjects.map((project, index) => (
                                    <div key={project} className="bg-background/60 flex items-center gap-3 rounded-xl border px-4 py-3">
                                        <span className="bg-primary/10 text-primary flex size-7 items-center justify-center rounded-full text-sm font-semibold">
                                            {index + 1}
                                        </span>
                                        <span className="font-medium">{project}</span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Backup & Kesehatan Sistem</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2">
                            <div className="bg-background/60 rounded-xl border p-4">
                                <IconBox icon={DatabaseBackup} />
                                <div className="mt-4 font-medium">Backup Restore</div>
                                <p className="text-muted-foreground mt-2 text-sm leading-5">
                                    Backup setting, database, dan server archive tersedia dari Console.
                                </p>
                            </div>
                            <div className="bg-background/60 rounded-xl border p-4">
                                <IconBox icon={HeartPulse} tone="success" />
                                <div className="mt-4 font-medium">System Health</div>
                                <p className="text-muted-foreground mt-2 text-sm leading-5">
                                    Health panel tersedia di System Settings untuk membaca kondisi runtime.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AppLayout>
    );
}
