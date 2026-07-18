import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Building2, FileText, MapPin, ShieldCheck, UsersRound } from 'lucide-react';
import type { HeadcountReport, HeadcountReportRow, HRReportsPageProps } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'HR Reports',
        href: '/hr/reports',
    },
];

export default function HRReportsIndex({ meta, filters, options, headcount }: HRReportsPageProps) {
    const updateFilter = (key: keyof HRReportsPageProps['filters'], value: string) => {
        router.get(
            route('hr.reports.index'),
            {
                ...filters,
                [key]: value === 'all' ? '' : value,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HR Reports" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-4 p-4 sm:p-6">
                <Card>
                    <CardHeader className="space-y-3">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="size-5 text-cyan-500" />
                                    HR Reports
                                </CardTitle>
                                <CardDescription>
                                    Boundary awal laporan HR. Module ini hanya membaca data dan belum membuka action mutasi atau export.
                                </CardDescription>
                            </div>
                            <Badge variant="secondary" className="gap-1">
                                <ShieldCheck className="size-3.5" />
                                {meta.status}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        {meta.scope.map((item) => (
                            <div key={item} className="bg-muted/30 rounded-xl border p-3 text-sm">
                                {item}
                            </div>
                        ))}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Filter report</CardTitle>
                        <CardDescription>Tanggal acuan wajib eksplisit agar hasil laporan bisa direproduksi.</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="hr-report-as-of">Tanggal acuan</Label>
                            <Input
                                id="hr-report-as-of"
                                type="date"
                                value={filters.as_of}
                                onChange={(event) => updateFilter('as_of', event.target.value)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>Status kerja</Label>
                            <Select
                                value={filters.employment_status_id ? String(filters.employment_status_id) : 'all'}
                                onValueChange={(value) => updateFilter('employment_status_id', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Semua status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua status</SelectItem>
                                    {options.employmentStatuses.map((status) => (
                                        <SelectItem key={status.value} value={String(status.value)}>
                                            {status.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <section className="grid gap-4 md:grid-cols-3">
                    <SummaryCard title="Total by Departement" value={headcount.byDepartement.total} icon={Building2} />
                    <SummaryCard title="Total by Work Location" value={headcount.byWorkLocation.total} icon={MapPin} />
                    <SummaryCard title="Total by Employment Status" value={headcount.byEmploymentStatus.total} icon={UsersRound} />
                </section>

                <section className="grid gap-4 xl:grid-cols-3">
                    <HeadcountTable title="Headcount by Departement" report={headcount.byDepartement} />
                    <HeadcountTable title="Headcount by Work Location" report={headcount.byWorkLocation} />
                    <HeadcountTable title="Employment Status Summary" report={headcount.byEmploymentStatus} />
                </section>
            </div>
        </AppLayout>
    );
}

function SummaryCard({ title, value, icon: Icon }: { title: string; value: number; icon: React.ComponentType<{ className?: string }> }) {
    return (
        <Card>
            <CardContent className="flex items-center gap-4 p-4">
                <div className="bg-primary/10 text-primary flex size-10 items-center justify-center rounded-lg">
                    <Icon className="size-5" />
                </div>
                <div>
                    <p className="text-muted-foreground text-sm">{title}</p>
                    <p className="text-2xl font-semibold">{value}</p>
                </div>
            </CardContent>
        </Card>
    );
}

function HeadcountTable({ title, report }: { title: string; report: HeadcountReport }) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>
                    As of {report.asOf} • Total {report.total}
                </CardDescription>
            </CardHeader>
            <CardContent>
                {report.rows.length === 0 ? (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-6 text-center text-sm">
                        Belum ada employee yang sesuai filter.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border">
                        <table className="w-full text-sm">
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3 font-medium">Group</th>
                                    <th className="p-3 text-right font-medium">Employee</th>
                                </tr>
                            </thead>
                            <tbody>
                                {report.rows.map((row) => (
                                    <HeadcountRow key={`${report.groupBy}-${row.id ?? 'unassigned'}`} row={row} />
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function HeadcountRow({ row }: { row: HeadcountReportRow }) {
    return (
        <tr className="border-t">
            <td className="p-3">
                <p className="font-medium">{row.name}</p>
                <p className="text-muted-foreground text-xs">{row.code ?? 'UNASSIGNED'}</p>
            </td>
            <td className="p-3 text-right font-semibold">{row.employeeCount}</td>
        </tr>
    );
}
