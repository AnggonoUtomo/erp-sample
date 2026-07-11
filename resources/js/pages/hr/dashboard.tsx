import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRight,
    BadgeCheck,
    BriefcaseBusiness,
    Building2,
    CheckCircle2,
    FileText,
    Layers3,
    ListFilter,
    MapPin,
    Network,
    ShieldCheck,
    UserRoundCog,
    UsersRound,
} from 'lucide-react';
import type { ComponentType } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR Dashboard',
        href: '/hr/dashboard',
    },
];

const quickActions = [
    {
        title: 'departements',
        href: '/hr/departements',
        icon: Building2,
        description: 'Kelola master Departement dan hierarchy dasar organisasi.',
        status: 'Ready',
    },
    {
        title: 'Positions',
        href: '/hr/positions',
        icon: UserRoundCog,
        description: 'Jabatan, job title, dan relasi ke Departement.',
        status: 'Ready',
    },
    {
        title: 'Job Levels',
        href: '/hr/job-levels',
        icon: Layers3,
        description: 'Level, grade, dan jenjang jabatan lintas Departement.',
        status: 'Ready',
    },
    {
        title: 'Work Locations',
        href: '/hr/work-locations',
        icon: MapPin,
        description: 'Lokasi kerja, area operasional, dan timezone attendance.',
        status: 'Ready',
    },
    {
        title: 'Employment Statuses',
        href: '/hr/employment-statuses',
        icon: BadgeCheck,
        description: 'Status kerja untuk lifecycle employee, attendance, dan payroll.',
        status: 'Ready',
    },
    {
        title: 'Employment Types',
        href: '/hr/employment-types',
        icon: BriefcaseBusiness,
        description: 'Tipe hubungan kerja untuk kontrak, benefit, overtime, dan payroll.',
        status: 'Ready',
    },
    {
        title: 'HR Reference Data',
        href: '/hr/hr-reference-data',
        icon: ListFilter,
        description: 'Referensi umum HR seperti gender, pendidikan, agama, bank, dan golongan darah.',
        status: 'Ready',
    },
    {
        title: 'Organization Structures',
        href: '/hr/organization-structures',
        icon: Network,
        description: 'Hierarchy organisasi untuk reporting line dan fondasi approval.',
        status: 'Ready',
    },
    {
        title: 'Employees',
        href: null,
        icon: UsersRound,
        description: 'Employee profile, avatar, work data, dan user link.',
        status: 'Planned',
    },
    {
        title: 'Documents',
        href: null,
        icon: FileText,
        description: 'Dokumen karyawan, kontrak, expiry reminder, dan akses file.',
        status: 'Planned',
    },
];

const foundationItems = [
    {
        title: 'Organization Foundation',
        description: 'Departements sudah menjadi titik awal struktur organisasi HR.',
        icon: Layers3,
    },
    {
        title: 'Access Boundary',
        description: 'Login, dashboard, route, dan permission HR memakai konteks project HR.',
        icon: ShieldCheck,
    },
    {
        title: 'Integration Ready',
        description: 'HR disiapkan sebagai upstream data employee untuk Attendance dan Payroll.',
        icon: Network,
    },
];

const roadmapItems = [
    { label: 'departements', status: 'Ready', icon: CheckCircle2 },
    { label: 'Positions', status: 'Ready', icon: CheckCircle2 },
    { label: 'Job Levels', status: 'Ready', icon: CheckCircle2 },
    { label: 'Work Locations', status: 'Ready', icon: CheckCircle2 },
    { label: 'Employment Statuses', status: 'Ready', icon: CheckCircle2 },
    { label: 'Employment Types', status: 'Ready', icon: CheckCircle2 },
    { label: 'HR Reference Data', status: 'Ready', icon: CheckCircle2 },
    { label: 'Organization Structures', status: 'Ready', icon: CheckCircle2 },
    { label: 'Employees', status: 'Planned', icon: UsersRound },
];

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

export default function HRDashboard() {
    const { auth } = usePage<SharedData>().props;
    const permissionCount = Object.keys(auth.permissions ?? {}).length;
    const roleCount = Object.keys(auth.roles ?? {}).length;

    const summaryCards = [
        {
            title: 'Project Scope',
            value: 'HR',
            description: 'Login, dashboard, dan module memakai prefix HR.',
            icon: Building2,
        },
        {
            title: 'Module Aktif',
            value: '8',
            description:
                'Departements, Positions, Job Levels, Work Locations, Employment Statuses, Employment Types, HR Reference Data, dan Organization Structures sudah tersedia.',
            icon: Layers3,
        },
        {
            title: 'Permission User',
            value: String(permissionCount),
            description: 'Permission efektif untuk session HR saat ini.',
            icon: ShieldCheck,
        },
        {
            title: 'Role User',
            value: String(roleCount),
            description: 'Role aktif yang diberikan ke akun login.',
            icon: UsersRound,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HR Dashboard" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <section className="bg-card rounded-lg border p-5 shadow-sm">
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-start gap-4">
                            <IconBox icon={Building2} tone="success" />
                            <div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <h1 className="text-2xl font-semibold tracking-tight">HR Dashboard</h1>
                                    <Badge variant="secondary">People Operation</Badge>
                                </div>
                                <p className="text-muted-foreground mt-1 max-w-3xl text-sm leading-6">
                                    Ringkasan project HR untuk mengelola struktur organisasi, employee master, dokumen, dan integrasi ke Attendance
                                    serta Payroll.
                                </p>
                            </div>
                        </div>

                        <Button asChild>
                            <Link href="/hr/departements">
                                Buka Departements
                                <ArrowRight className="size-4" />
                            </Link>
                        </Button>
                    </div>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map((item) => (
                        <Card key={item.title}>
                            <CardContent className="flex gap-4 p-4">
                                <IconBox icon={item.icon} />
                                <div>
                                    <p className="text-sm font-medium">{item.title}</p>
                                    <p className="mt-2 text-2xl font-semibold">{item.value}</p>
                                    <p className="text-muted-foreground mt-1 text-xs leading-5">{item.description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <p className="text-muted-foreground text-sm">Module kerja utama di project HR.</p>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2">
                            {quickActions.map((item) => {
                                const content = (
                                    <div className="hover:border-primary/35 hover:bg-muted/30 flex h-full gap-4 rounded-lg border p-4 transition">
                                        <IconBox icon={item.icon} tone={item.href ? 'success' : 'warning'} />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center justify-between gap-2">
                                                <p className="font-semibold">{item.title}</p>
                                                <Badge variant={item.href ? 'default' : 'secondary'}>{item.status}</Badge>
                                            </div>
                                            <p className="text-muted-foreground mt-2 text-sm leading-6">{item.description}</p>
                                        </div>
                                    </div>
                                );

                                return item.href ? (
                                    <Link key={item.title} href={item.href}>
                                        {content}
                                    </Link>
                                ) : (
                                    <div key={item.title}>{content}</div>
                                );
                            })}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Roadmap HR</CardTitle>
                            <p className="text-muted-foreground text-sm">Urutan module yang sedang disiapkan.</p>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {roadmapItems.map((item) => {
                                const Icon = item.icon;

                                return (
                                    <div key={item.label} className="flex items-center gap-3 rounded-lg border p-3">
                                        <Icon className="size-4 text-emerald-600" />
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-medium">{item.label}</p>
                                            <p className="text-muted-foreground text-xs">{item.status}</p>
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 lg:grid-cols-3">
                    {foundationItems.map((item) => (
                        <Card key={item.title}>
                            <CardContent className="flex gap-4 p-5">
                                <IconBox icon={item.icon} tone="success" />
                                <div>
                                    <p className="font-semibold">{item.title}</p>
                                    <p className="text-muted-foreground mt-1 text-sm leading-6">{item.description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>
            </div>
        </AppLayout>
    );
}
