import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowUpRight,
    BadgeCheck,
    BarChart3,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    CheckCircle2,
    ClipboardCheck,
    FileSignature,
    FileText,
    Layers3,
    ListChecks,
    ListFilter,
    MapPin,
    Network,
    Route,
    ShieldCheck,
    UserRoundCog,
    UsersRound,
    Workflow,
} from 'lucide-react';
import type { ComponentType } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dasbor HR',
        href: '/hr/dashboard',
    },
];

const masterDataLinks = [
    ['Departemen', '/hr/departements', Building2],
    ['Posisi', '/hr/positions', UserRoundCog],
    ['Level Jabatan', '/hr/job-levels', Layers3],
    ['Lokasi Kerja', '/hr/work-locations', MapPin],
    ['Status Kepegawaian', '/hr/employment-statuses', BadgeCheck],
    ['Tipe Kepegawaian', '/hr/employment-types', BriefcaseBusiness],
    ['Data Referensi HR', '/hr/hr-reference-data', ListFilter],
    ['Struktur Organisasi', '/hr/organization-structures', Network],
] as const;

const employeeLifecycleLinks = [
    ['Karyawan', '/hr/employees', UsersRound, 'Profile dan assignment utama'],
    ['Kontrak Karyawan', '/hr/employee-contracts', FileSignature, 'Effective-dated contract'],
    ['Mutasi Karyawan', '/hr/employee-movements', Route, 'Transfer, promosi, status'],
    ['Dokumen Karyawan', '/hr/employee-documents', FileText, 'Metadata dan expiry'],
    ['Onboarding Karyawan', '/hr/onboardings', ClipboardCheck, 'Checklist masuk kerja'],
    ['Offboarding Karyawan', '/hr/offboardings', ListChecks, 'Checklist exit dan finalisasi'],
] as const;

const readiness = [
    { title: 'Master Data', done: 8, total: 8, icon: Building2 },
    { title: 'Employee Lifecycle', done: 6, total: 6, icon: UsersRound },
    { title: 'Reports MVP', done: 5, total: 5, icon: BarChart3 },
    { title: 'Integration Contract', done: 4, total: 4, icon: Workflow },
];

function IconBox({ icon: Icon, tone = 'blue' }: { icon: ComponentType<{ className?: string }>; tone?: 'blue' | 'green' | 'amber' | 'violet' }) {
    const tones = {
        amber: 'bg-amber-500/12 text-amber-600 dark:text-amber-400',
        blue: 'bg-blue-500/12 text-blue-600 dark:text-blue-400',
        green: 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400',
        violet: 'bg-violet-500/12 text-violet-600 dark:text-violet-400',
    };

    return (
        <div className={`flex size-11 shrink-0 items-center justify-center rounded-xl ${tones[tone]}`}>
            <Icon className="size-5" />
        </div>
    );
}

export default function HRDashboard() {
    const { auth } = usePage<SharedData>().props;
    const permissionCount = Object.keys(auth.permissions ?? {}).length;
    const roleCount = Object.keys(auth.roles ?? {}).length;
    const today = new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(new Date());

    const summaryCards = [
        ['Scope HR', 'People Ops', 'Module HR sebagai upstream Attendance dan Payroll.', Building2, 'blue'],
        ['Module Siap', '20+', 'Master data, employee lifecycle, reports, dan integration contract.', CheckCircle2, 'green'],
        ['Permission User', String(permissionCount), 'Permission efektif untuk session HR saat ini.', ShieldCheck, 'violet'],
        ['Role User', String(roleCount), 'Role aktif yang diberikan ke akun login.', UsersRound, 'amber'],
    ] as const;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dasbor HR" />

            <div className="mx-auto flex w-full max-w-[1440px] flex-1 flex-col gap-5 p-4 sm:p-6">
                <section className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <Badge variant="secondary" className="rounded-full">
                            Human Resource
                        </Badge>
                        <h1 className="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Selamat datang di pusat operasional HR</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Kelola master organisasi, data karyawan, kontrak, dokumen, onboarding, offboarding, laporan, dan integration contract.
                        </p>
                    </div>
                    <Button variant="outline" className="w-fit rounded-xl">
                        <CalendarDays className="size-4" />
                        {today}
                    </Button>
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {summaryCards.map(([title, value, description, icon, tone]) => (
                        <Card key={title}>
                            <CardContent className="flex gap-4 p-5">
                                <IconBox icon={icon} tone={tone} />
                                <div className="min-w-0">
                                    <p className="text-muted-foreground text-sm">{title}</p>
                                    <p className="mt-1 text-2xl font-semibold">{value}</p>
                                    <p className="text-muted-foreground mt-1 text-xs leading-5">{description}</p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)]">
                    <Card>
                        <CardHeader className="flex-row items-center justify-between space-y-0">
                            <div>
                                <CardTitle>Employee Lifecycle</CardTitle>
                                <p className="text-muted-foreground mt-1 text-sm">Alur utama karyawan dari masuk, aktif, berubah, hingga exit.</p>
                            </div>
                            <Button variant="ghost" size="sm" asChild>
                                <Link href="/hr/reports">
                                    Laporan HR
                                    <ArrowUpRight className="size-4" />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent className="grid gap-3 md:grid-cols-2">
                            {employeeLifecycleLinks.map(([title, href, icon, description], index) => (
                                <Link key={title} href={href} className="hover:border-primary/40 hover:bg-primary/5 rounded-xl border p-4 transition">
                                    <div className="flex gap-3">
                                        <IconBox icon={icon} tone={index % 2 === 0 ? 'blue' : 'green'} />
                                        <div className="min-w-0">
                                            <p className="font-semibold">{title}</p>
                                            <p className="text-muted-foreground mt-1 text-sm leading-5">{description}</p>
                                        </div>
                                    </div>
                                </Link>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Kesiapan HR</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {readiness.map((item, index) => {
                                const percent = Math.round((item.done / item.total) * 100);

                                return (
                                    <div key={item.title} className="flex items-center gap-3">
                                        <IconBox icon={item.icon} tone={index === 3 ? 'violet' : 'blue'} />
                                        <div className="min-w-0 flex-1">
                                            <div className="flex items-center justify-between gap-3">
                                                <p className="truncate text-sm font-medium">{item.title}</p>
                                                <p className="text-sm font-semibold">{percent}%</p>
                                            </div>
                                            <div className="bg-muted mt-2 h-2 rounded-full">
                                                <div className="bg-primary h-2 rounded-full" style={{ width: `${percent}%` }} />
                                            </div>
                                            <p className="text-muted-foreground mt-1 text-xs">
                                                {item.done}/{item.total} area MVP siap
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </CardContent>
                    </Card>
                </section>

                <section className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <Card>
                        <CardHeader>
                            <CardTitle>Master Data HR</CardTitle>
                            <p className="text-muted-foreground text-sm">Isi bagian ini dulu sebelum membuat karyawan dan lifecycle.</p>
                        </CardHeader>
                        <CardContent className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            {masterDataLinks.map(([title, href, icon]) => (
                                <Link key={title} href={href} className="hover:border-primary/40 hover:bg-primary/5 rounded-xl border p-4 transition">
                                    <IconBox icon={icon} tone="green" />
                                    <p className="mt-3 text-sm font-semibold">{title}</p>
                                </Link>
                            ))}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Output HR</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {[
                                'Snapshot karyawan untuk Attendance dan Payroll.',
                                'Ringkasan headcount, kontrak, dan dokumen expiry.',
                                'Event contract tanpa mutasi downstream spekulatif.',
                                'Histori movement before/after untuk audit HR.',
                            ].map((item) => (
                                <div key={item} className="flex gap-3 rounded-xl border p-3">
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0 text-emerald-600" />
                                    <p className="text-sm leading-5">{item}</p>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </section>
            </div>
        </AppLayout>
    );
}
