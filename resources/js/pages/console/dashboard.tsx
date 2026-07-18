import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Activity,
    ArchiveRestore,
    ArrowUpRight,
    BarChart3,
    Boxes,
    CalendarDays,
    DatabaseBackup,
    FileClock,
    FileText,
    KeyRound,
    Route,
    ServerCog,
    ShieldCheck,
    Users,
    Workflow,
} from 'lucide-react';
import { type ComponentType } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor',
        href: '/dashboard',
    },
];

const operations = [
    { title: 'Module Registry', done: 18, total: 18, icon: Boxes },
    { title: 'Permission Contract', done: 94, total: 100, icon: ShieldCheck },
    { title: 'Backup Integrity', done: 100, total: 100, icon: DatabaseBackup },
    { title: 'Queue & Scheduler', done: 76, total: 100, icon: ServerCog },
];

const recentActivities = [
    {
        title: 'HR Integration Contract siap dipakai consumer',
        actor: 'Project HR',
        date: 'Checkpoint terbaru',
        icon: Workflow,
    },
    {
        title: 'Dashboard dan sidebar dipoles mengikuti konsep ERP',
        actor: 'Console UI',
        date: 'Sedang berjalan',
        icon: BarChart3,
    },
    {
        title: 'DMS foundation menjadi storage engine dokumen',
        actor: 'Document Management',
        date: 'MVP secure',
        icon: FileText,
    },
    {
        title: 'Backup restore memakai checksum dan signature',
        actor: 'System Console',
        date: 'Hardening',
        icon: ArchiveRestore,
    },
];

const projectRows = [
    ['HR-REPORT', 'Human Resource', 'MVP reports complete', 'Selesai'],
    ['HR-INT', 'Integration Contract', 'Snapshot dan event handoff', 'Selesai'],
    ['DMS-MVP', 'Document Management', 'Private storage foundation', 'Selesai'],
    ['ATT-NEXT', 'Attendance', 'Consumer HR contract', 'Berikutnya'],
    ['PAY-NEXT', 'Payroll', 'Consumer HR contract', 'Berikutnya'],
];

const foundationHighlights: Array<{
    title: string;
    description: string;
    icon: ComponentType<{ className?: string }>;
}> = [
    {
        title: 'Akses terkontrol',
        description: 'Permission modular menjaga fitur hanya muncul untuk role berizin.',
        icon: ShieldCheck,
    },
    {
        title: 'Arsitektur modular',
        description: 'Project besar dipisahkan ke module dan integration contract yang stabil.',
        icon: Workflow,
    },
    {
        title: 'Audit siap baca',
        description: 'Activity, login, queue, scheduler, backup, dan restore tetap menjadi kontrol operasional.',
        icon: FileClock,
    },
];

function IconBox({
    icon: Icon,
    tone = 'blue',
}: {
    icon: ComponentType<{ className?: string }>;
    tone?: 'blue' | 'green' | 'amber' | 'rose' | 'violet';
}) {
    const tones = {
        amber: 'bg-amber-500/12 text-amber-600 dark:text-amber-400',
        blue: 'bg-blue-500/12 text-blue-600 dark:text-blue-400',
        green: 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400',
        rose: 'bg-rose-500/12 text-rose-600 dark:text-rose-400',
        violet: 'bg-violet-500/12 text-violet-600 dark:text-violet-400',
    };

    return (
        <div className={`flex size-11 shrink-0 items-center justify-center rounded-xl ${tones[tone]}`}>
            <Icon className="size-5" />
        </div>
    );
}

function MiniLineChart() {
    return (
        <div className="from-background to-muted/35 relative h-56 overflow-hidden rounded-xl border bg-gradient-to-b p-4">
            <div className="text-muted-foreground absolute top-4 left-4 space-y-8 text-xs">
                <div>100%</div>
                <div>75%</div>
                <div>50%</div>
                <div>25%</div>
            </div>
            <svg viewBox="0 0 640 220" className="h-full w-full pl-10" role="img" aria-label="Grafik kesiapan project enam bulan terakhir">
                <defs>
                    <linearGradient id="console-line-fill" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stopColor="var(--primary)" stopOpacity="0.22" />
                        <stop offset="100%" stopColor="var(--primary)" stopOpacity="0" />
                    </linearGradient>
                </defs>
                {[40, 80, 120, 160, 200].map((y) => (
                    <line key={y} x1="16" x2="620" y1={y} y2={y} stroke="currentColor" className="text-border" strokeDasharray="4 6" />
                ))}
                <path
                    d="M20 168 C96 122 132 126 190 92 C260 52 318 94 380 70 C456 40 500 88 620 48"
                    fill="none"
                    stroke="var(--primary)"
                    strokeWidth="4"
                />
                <path
                    d="M20 168 C96 122 132 126 190 92 C260 52 318 94 380 70 C456 40 500 88 620 48 L620 210 L20 210 Z"
                    fill="url(#console-line-fill)"
                />
                <path
                    d="M20 188 C116 152 152 154 214 132 C286 104 344 142 404 116 C476 86 522 126 620 98"
                    fill="none"
                    stroke="var(--chart-2)"
                    strokeWidth="3"
                    opacity="0.85"
                />
                {[
                    [20, 168],
                    [190, 92],
                    [380, 70],
                    [620, 48],
                ].map(([x, y]) => (
                    <circle key={`${x}-${y}`} cx={x} cy={y} r="5" fill="var(--primary)" stroke="var(--card)" strokeWidth="3" />
                ))}
            </svg>
            <div className="text-muted-foreground mt-2 flex justify-between px-10 text-xs">
                <span>Feb</span>
                <span>Mar</span>
                <span>Apr</span>
                <span>Mei</span>
                <span>Jun</span>
                <span>Jul</span>
            </div>
        </div>
    );
}

export default function Dashboard() {
    const { props } = usePage<SharedData>();
    const userName = props.auth.user?.name ?? 'Admin';
    const moduleCount = props.navigation.reduce((total, group) => total + group.items.length, 0);
    const unreadActivities = props.activity_center.unread_count;
    const permissionsCount = Object.keys(props.auth.permissions ?? {}).length;
    const roleCount = Object.keys(props.auth.roles ?? {}).length;
    const today = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());

    const summaryCards = [
        {
            title: 'Module Aktif',
            value: String(moduleCount),
            change: '+ stabil',
            detail: 'Terdaftar lewat registry module',
            icon: Route,
            tone: 'blue' as const,
        },
        {
            title: 'Permission User',
            value: String(permissionsCount),
            change: 'session',
            detail: 'Permission efektif akun login',
            icon: KeyRound,
            tone: 'violet' as const,
        },
        {
            title: 'Role User',
            value: String(roleCount),
            change: props.auth.super ? 'super admin' : 'aktif',
            detail: 'Role yang dibagikan ke Inertia',
            icon: Users,
            tone: 'green' as const,
        },
        {
            title: 'Aktivitas Baru',
            value: String(unreadActivities),
            change: 'activity center',
            detail: 'Notifikasi yang belum dibaca',
            icon: Activity,
            tone: unreadActivities > 0 ? ('amber' as const) : ('green' as const),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dasbor Console" />

            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-5 p-4 sm:p-6">
                <section className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">Selamat datang kembali, {userName}</h1>
                        <p className="text-muted-foreground mt-1 text-sm">Ringkasan operasional ERP modular dan fondasi sistem hari ini.</p>
                    </div>
                    <Button variant="outline" className="w-fit rounded-xl">
                        <CalendarDays className="size-4" />
                        {today}
                    </Button>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map((item) => (
                        <Card key={item.title} className="overflow-hidden">
                            <CardContent className="flex items-center gap-4 p-5">
                                <IconBox icon={item.icon} tone={item.tone} />
                                <div className="min-w-0">
                                    <p className="text-muted-foreground text-sm">{item.title}</p>
                                    <div className="mt-1 flex items-center gap-2">
                                        <p className="text-2xl font-semibold">{item.value}</p>
                                        <Badge variant="secondary" className="rounded-full text-[11px]">
                                            {item.change}
                                        </Badge>
                                    </div>
                                    <p className="text-muted-foreground mt-1 truncate text-xs">{item.detail}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)]">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Kesiapan Project</CardTitle>
                                <p className="text-muted-foreground mt-1 text-sm">Progress fondasi modular, HR, DMS, dan handoff consumer.</p>
                            </div>
                            <Badge variant="secondary" className="rounded-full">
                                6 bulan terakhir
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <MiniLineChart />
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Status Operasional</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {operations.map((item, index) => {
                                const percent = Math.round((item.done / item.total) * 100);

                                return (
                                    <div key={item.title} className="space-y-2">
                                        <div className="flex items-center gap-3">
                                            <IconBox icon={item.icon} tone={index === 3 ? 'amber' : 'blue'} />
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-center justify-between gap-3">
                                                    <p className="truncate text-sm font-medium">{item.title}</p>
                                                    <p className="text-sm font-semibold">{percent}%</p>
                                                </div>
                                                <div className="bg-muted mt-2 h-2 rounded-full">
                                                    <div className="bg-primary h-2 rounded-full" style={{ width: `${percent}%` }} />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Project Terbaru</CardTitle>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href="/hr/reports">
                                    Lihat laporan
                                    <ArrowUpRight className="size-4" />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent className="overflow-x-auto">
                            <div className="min-w-[720px]">
                                <div className="text-muted-foreground grid grid-cols-[120px_1fr_1.2fr_120px] border-b pb-3 text-xs font-medium">
                                    <span>Kode</span>
                                    <span>Area</span>
                                    <span>Deskripsi</span>
                                    <span>Status</span>
                                </div>
                                {projectRows.map(([code, area, description, status]) => (
                                    <div
                                        key={code}
                                        className="grid grid-cols-[120px_1fr_1.2fr_120px] items-center border-b py-3 text-sm last:border-0"
                                    >
                                        <span className="font-medium">{code}</span>
                                        <span>{area}</span>
                                        <span className="text-muted-foreground">{description}</span>
                                        <span>
                                            <Badge variant={status === 'Selesai' ? 'default' : 'secondary'}>{status}</Badge>
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Aktivitas Terbaru</CardTitle>
                            <Badge variant="secondary" className="rounded-full">
                                Live
                            </Badge>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {recentActivities.map((item, index) => (
                                <div key={item.title} className="flex gap-3">
                                    <IconBox icon={item.icon} tone={index === 1 ? 'violet' : index === 3 ? 'amber' : 'green'} />
                                    <div className="min-w-0 flex-1 border-b pb-4 last:border-0 last:pb-0">
                                        <p className="text-sm leading-5 font-medium">{item.title}</p>
                                        <p className="text-muted-foreground mt-1 text-xs">
                                            oleh {item.actor} · {item.date}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 md:grid-cols-3">
                    {foundationHighlights.map(({ title, description, icon }) => (
                        <Card key={title}>
                            <CardContent className="flex gap-4 p-5">
                                <IconBox icon={icon} />
                                <div>
                                    <p className="font-semibold">{title}</p>
                                    <p className="text-muted-foreground mt-1 text-sm leading-6">{description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>
            </div>
        </AppLayout>
    );
}
