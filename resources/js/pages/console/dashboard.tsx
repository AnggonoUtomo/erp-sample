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
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    FileClock,
    FileText,
    FolderKanban,
    KeyRound,
    Layers3,
    MapPin,
    Network,
    Route,
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

interface RecentAuditLog {
    id: number;
    module: string;
    event: string;
    description: string | null;
    created_at_human: string | null;
}

interface RecentLoginActivity {
    id: number;
    email: string;
    event: string;
    successful: boolean;
    occurred_at_human: string | null;
}

interface DashboardOverview {
    access: {
        console_admin: boolean;
        hr: boolean;
        dms: boolean;
        audit: boolean;
        login_activities: boolean;
    };
    console: {
        modules: number;
        console_modules: number;
        hr_modules: number;
        permissions: number;
        roles: number;
        users: number;
    };
    hr: {
        employees: number;
        active_employees: number;
        departements: number;
        positions: number;
        job_levels: number;
        work_locations: number;
        employment_statuses: number;
        employment_types: number;
        reference_data: number;
        organization_nodes: number;
        contracts: number;
        documents: number;
        movements: number;
        onboardings: number;
        offboardings: number;
    };
    dms: {
        documents: number;
        versions: number;
        available_versions: number;
        quarantined_versions: number;
    };
    activity: {
        audit_logs: number;
        login_activities: number;
        recent_audit_logs: RecentAuditLog[];
        recent_login_activities: RecentLoginActivity[];
    };
}

type DashboardPageProps = SharedData & {
    dashboard: DashboardOverview;
};

type IconTone = 'amber' | 'blue' | 'emerald' | 'fuchsia' | 'rose' | 'sky' | 'violet';

function IconBox({ icon: Icon, tone = 'blue' }: { icon: ComponentType<{ className?: string }>; tone?: IconTone }) {
    const tones: Record<IconTone, string> = {
        amber: 'icon-tone-amber',
        blue: 'icon-tone-indigo',
        emerald: 'icon-tone-emerald',
        fuchsia: 'icon-tone-fuchsia',
        rose: 'icon-tone-rose',
        sky: 'icon-tone-sky',
        violet: 'icon-tone-violet',
    };

    return (
        <div className={`dashboard-icon ${tones[tone]} flex size-11 shrink-0 items-center justify-center rounded-xl`}>
            <Icon className="size-5" />
        </div>
    );
}

function formatNumber(value: number) {
    return new Intl.NumberFormat('id-ID').format(value);
}

function ReadinessChart({ hrScore, consoleScore, dmsScore }: { hrScore: number; consoleScore: number; dmsScore: number }) {
    const bars = [
        { label: 'Console', value: consoleScore, tone: 'bg-indigo-500' },
        { label: 'HR', value: hrScore, tone: 'bg-emerald-500' },
        { label: 'DMS', value: dmsScore, tone: 'bg-sky-500' },
    ];

    return (
        <div className="from-background to-muted/35 rounded-xl border bg-gradient-to-b p-5">
            <div className="grid gap-4">
                {bars.map((bar) => (
                    <div key={bar.label} className="space-y-2">
                        <div className="flex items-center justify-between text-sm">
                            <span className="font-medium">{bar.label}</span>
                            <span className="text-muted-foreground">{bar.value}%</span>
                        </div>
                        <div className="bg-muted h-3 rounded-full">
                            <div className={`${bar.tone} h-3 rounded-full`} style={{ width: `${bar.value}%` }} />
                        </div>
                    </div>
                ))}
            </div>
            <div className="text-muted-foreground mt-5 grid grid-cols-3 gap-2 text-center text-xs">
                <span>Foundation</span>
                <span>Lifecycle</span>
                <span>Readiness</span>
            </div>
        </div>
    );
}

export default function Dashboard() {
    const { props } = usePage<DashboardPageProps>();
    const { dashboard } = props;
    const userName = props.auth.user?.name ?? 'Admin';
    const unreadActivities = props.activity_center.unread_count;
    const today = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());

    const masterDataTotal =
        dashboard.hr.departements +
        dashboard.hr.positions +
        dashboard.hr.job_levels +
        dashboard.hr.work_locations +
        dashboard.hr.employment_statuses +
        dashboard.hr.employment_types +
        dashboard.hr.reference_data +
        dashboard.hr.organization_nodes;

    const lifecycleTotal =
        dashboard.hr.contracts + dashboard.hr.documents + dashboard.hr.movements + dashboard.hr.onboardings + dashboard.hr.offboardings;

    const summaryCards = [
        {
            title: 'Console',
            value: formatNumber(dashboard.console.modules),
            change: dashboard.access.console_admin ? `${formatNumber(dashboard.console.permissions)} permission` : 'akses pribadi',
            detail: dashboard.access.console_admin
                ? `${formatNumber(dashboard.console.users)} user · ${formatNumber(dashboard.console.roles)} role`
                : `${formatNumber(dashboard.console.permissions)} permission efektif akun ini`,
            icon: Route,
            tone: 'blue' as const,
        },
        {
            title: 'HR Core',
            value: formatNumber(dashboard.hr.employees),
            change: dashboard.access.hr ? `${formatNumber(dashboard.hr.active_employees)} aktif` : 'akses dibatasi',
            detail: dashboard.access.hr ? `${formatNumber(masterDataTotal)} master data siap pakai` : 'Butuh permission HR untuk membaca KPI',
            icon: Users,
            tone: 'emerald' as const,
        },
        {
            title: 'Dokumen',
            value: formatNumber(dashboard.dms.documents),
            change: dashboard.access.dms ? `${formatNumber(dashboard.dms.available_versions)} tersedia` : 'akses dibatasi',
            detail: dashboard.access.dms
                ? `${formatNumber(dashboard.dms.versions)} versi · ${formatNumber(dashboard.dms.quarantined_versions)} karantina`
                : 'Butuh permission DMS untuk membaca KPI',
            icon: FileText,
            tone: dashboard.dms.quarantined_versions > 0 ? ('amber' as const) : ('sky' as const),
        },
        {
            title: 'Aktivitas',
            value: formatNumber(unreadActivities),
            change: dashboard.access.audit ? `${formatNumber(dashboard.activity.audit_logs)} audit` : 'akses terbatas',
            detail: dashboard.access.login_activities
                ? `${formatNumber(dashboard.activity.login_activities)} login activity`
                : 'Butuh permission observability untuk detail',
            icon: Activity,
            tone: unreadActivities > 0 ? ('amber' as const) : ('violet' as const),
        },
    ];

    const hrMasterRows = [
        { label: 'Departemen', value: dashboard.hr.departements, icon: Building2, href: '/hr/departements' },
        { label: 'Posisi', value: dashboard.hr.positions, icon: BriefcaseBusiness, href: '/hr/positions' },
        { label: 'Job Level', value: dashboard.hr.job_levels, icon: Layers3, href: '/hr/job-levels' },
        { label: 'Lokasi Kerja', value: dashboard.hr.work_locations, icon: MapPin, href: '/hr/work-locations' },
        { label: 'Status Kepegawaian', value: dashboard.hr.employment_statuses, icon: ShieldCheck, href: '/hr/employment-statuses' },
        { label: 'Tipe Kepegawaian', value: dashboard.hr.employment_types, icon: KeyRound, href: '/hr/employment-types' },
        { label: 'Referensi HR', value: dashboard.hr.reference_data, icon: FolderKanban, href: '/hr/hr-reference-data' },
        { label: 'Struktur Organisasi', value: dashboard.hr.organization_nodes, icon: Network, href: '/hr/organization-structures' },
    ];

    const lifecycleRows = [
        { label: 'Kontrak', value: dashboard.hr.contracts, icon: FileClock, href: '/hr/employee-contracts' },
        { label: 'Dokumen Employee', value: dashboard.hr.documents, icon: FileText, href: '/hr/employee-documents' },
        { label: 'Mutasi', value: dashboard.hr.movements, icon: Workflow, href: '/hr/employee-movements' },
        { label: 'Onboarding', value: dashboard.hr.onboardings, icon: BarChart3, href: '/hr/onboardings' },
        { label: 'Offboarding', value: dashboard.hr.offboardings, icon: ArchiveRestore, href: '/hr/offboardings' },
    ];

    const recentAuditLogs = dashboard.activity.recent_audit_logs.length > 0 ? dashboard.activity.recent_audit_logs : [];
    const recentLoginActivities = dashboard.activity.recent_login_activities.length > 0 ? dashboard.activity.recent_login_activities : [];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dasbor Console" />

            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-5 p-4 sm:p-6">
                <section className="overflow-hidden rounded-3xl border bg-[radial-gradient(circle_at_top_left,var(--primary)/0.14,transparent_34%),linear-gradient(135deg,var(--card),var(--muted)/0.34)] p-5 sm:p-6">
                    <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-3xl">
                            <Badge variant="secondary" className="mb-3 rounded-full">
                                ERP Modular · Console Overview
                            </Badge>
                            <h1 className="text-2xl font-semibold tracking-tight sm:text-3xl">Selamat datang kembali, {userName}</h1>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">
                                Ringkasan singkat untuk membaca kesehatan Console, kesiapan HR, fondasi dokumen, dan aktivitas sistem tanpa masuk ke
                                tiap menu satu per satu.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Button variant="secondary" className="rounded-xl" type="button">
                                <ShieldCheck className="size-4" />
                                Ctrl K · Command Palette
                            </Button>
                            <Button variant="outline" className="rounded-xl">
                                <CalendarDays className="size-4" />
                                {today}
                            </Button>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map((item) => (
                        <Card key={item.title} data-dashboard-card className="overflow-hidden">
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

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(340px,0.8fr)]">
                    <Card data-dashboard-card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Kesiapan MVP</CardTitle>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    Dibaca dari registry module, lifecycle HR, dan status version DMS.
                                </p>
                            </div>
                            <Badge variant="secondary" className="rounded-full">
                                Read-only
                            </Badge>
                        </CardHeader>
                        <CardContent>
                            <ReadinessChart
                                consoleScore={dashboard.console.modules > 0 ? 100 : 0}
                                hrScore={dashboard.access.hr ? (dashboard.hr.employees > 0 || masterDataTotal > 0 ? 100 : 65) : 0}
                                dmsScore={dashboard.access.dms ? (dashboard.dms.documents > 0 || dashboard.dms.versions > 0 ? 100 : 80) : 0}
                            />
                        </CardContent>
                    </Card>

                    <Card data-dashboard-card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Jalur Berikutnya</CardTitle>
                            <Badge variant="outline" className="rounded-full">
                                MVP map
                            </Badge>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {[
                                ['Polish dashboard', 'Ringkasan operasional dari Console + HR + DMS.', 'Berjalan'],
                                ['Review HR foundation', 'Telusuri master data HR satu per satu.', 'Next'],
                                ['SOP dev workflow', 'Branch, commit, migration, seed, test, deploy lokal.', 'Next'],
                                ['Attendance', 'Ditahan sampai HR foundation makin matang.', 'Hold'],
                            ].map(([title, description, status]) => (
                                <div key={title} className="bg-muted/25 rounded-2xl border p-3">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-sm font-medium">{title}</p>
                                            <p className="text-muted-foreground mt-1 text-xs leading-5">{description}</p>
                                        </div>
                                        <Badge variant={status === 'Berjalan' ? 'default' : 'secondary'} className="rounded-full text-[11px]">
                                            {status}
                                        </Badge>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <Card data-dashboard-card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>HR Foundation</CardTitle>
                            {dashboard.access.hr ? (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href="/hr/reports">
                                        Laporan HR
                                        <ArrowUpRight className="size-4" />
                                    </Link>
                                </Button>
                            ) : null}
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2">
                            {!dashboard.access.hr ? (
                                <p className="text-muted-foreground bg-muted/20 rounded-2xl border p-4 text-sm sm:col-span-2">
                                    KPI HR disembunyikan untuk akun tanpa permission HR.
                                </p>
                            ) : (
                                hrMasterRows.map((item, index) => (
                                    <Link key={item.label} href={item.href} className="bg-muted/20 hover:bg-accent rounded-2xl border p-3 transition">
                                        <div className="flex items-center gap-3">
                                            <IconBox icon={item.icon} tone={index % 3 === 0 ? 'emerald' : index % 3 === 1 ? 'sky' : 'violet'} />
                                            <div>
                                                <p className="text-sm font-medium">{item.label}</p>
                                                <p className="text-muted-foreground text-xs">{formatNumber(item.value)} record</p>
                                            </div>
                                        </div>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card data-dashboard-card>
                        <CardHeader>
                            <CardTitle>Lifecycle Employee</CardTitle>
                            <p className="text-muted-foreground mt-1 text-sm">{formatNumber(lifecycleTotal)} record lifecycle HR terbaca.</p>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {!dashboard.access.hr ? (
                                <p className="text-muted-foreground bg-muted/20 rounded-2xl border p-4 text-sm">
                                    Lifecycle HR disembunyikan untuk akun tanpa permission HR.
                                </p>
                            ) : (
                                lifecycleRows.map((item, index) => (
                                    <Link
                                        key={item.label}
                                        href={item.href}
                                        className="bg-muted/20 hover:bg-accent flex items-center justify-between rounded-2xl border p-3 transition"
                                    >
                                        <div className="flex items-center gap-3">
                                            <IconBox icon={item.icon} tone={index % 2 === 0 ? 'fuchsia' : 'amber'} />
                                            <span className="text-sm font-medium">{item.label}</span>
                                        </div>
                                        <Badge variant="secondary" className="rounded-full">
                                            {formatNumber(item.value)}
                                        </Badge>
                                    </Link>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
                    <Card data-dashboard-card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Audit Terbaru</CardTitle>
                            {dashboard.access.audit ? (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href="/audit-logs">
                                        Buka audit
                                        <ArrowUpRight className="size-4" />
                                    </Link>
                                </Button>
                            ) : null}
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {recentAuditLogs.length === 0 ? (
                                <p className="text-muted-foreground bg-muted/20 rounded-2xl border p-4 text-sm">
                                    {dashboard.access.audit ? 'Belum ada audit log terbaru.' : 'Audit log disembunyikan untuk akun tanpa permission.'}
                                </p>
                            ) : (
                                recentAuditLogs.map((item) => (
                                    <div key={item.id} className="bg-muted/20 rounded-2xl border p-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0">
                                                <p className="truncate text-sm font-medium">{item.description ?? item.event}</p>
                                                <p className="text-muted-foreground mt-1 text-xs">
                                                    {item.module} · {item.event}
                                                </p>
                                            </div>
                                            <span className="text-muted-foreground shrink-0 text-xs">{item.created_at_human ?? '-'}</span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>

                    <Card data-dashboard-card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <CardTitle>Login Activity</CardTitle>
                            {dashboard.access.login_activities ? (
                                <Button variant="ghost" size="sm" asChild>
                                    <Link href="/login-activities">
                                        Buka login
                                        <ArrowUpRight className="size-4" />
                                    </Link>
                                </Button>
                            ) : null}
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {recentLoginActivities.length === 0 ? (
                                <p className="text-muted-foreground bg-muted/20 rounded-2xl border p-4 text-sm">
                                    {dashboard.access.login_activities
                                        ? 'Belum ada aktivitas login terbaru.'
                                        : 'Login activity disembunyikan untuk akun tanpa permission.'}
                                </p>
                            ) : (
                                recentLoginActivities.map((item) => (
                                    <div key={item.id} className="bg-muted/20 flex items-center justify-between gap-3 rounded-2xl border p-3">
                                        <div className="min-w-0">
                                            <p className="truncate text-sm font-medium">{item.email}</p>
                                            <p className="text-muted-foreground mt-1 text-xs">{item.event}</p>
                                        </div>
                                        <div className="flex shrink-0 items-center gap-2">
                                            <Badge variant={item.successful ? 'secondary' : 'destructive'} className="rounded-full">
                                                {item.successful ? 'Sukses' : 'Gagal'}
                                            </Badge>
                                            <span className="text-muted-foreground text-xs">{item.occurred_at_human ?? '-'}</span>
                                        </div>
                                    </div>
                                ))
                            )}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AppLayout>
    );
}
