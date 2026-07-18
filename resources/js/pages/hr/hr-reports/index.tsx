import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import { HRReportExpiryCard } from './hr-report-components/hr-report-expiry-card';
import { HRReportFilterCard } from './hr-report-components/hr-report-filter-card';
import { HRReportHeadcountCard } from './hr-report-components/hr-report-headcount-card';
import { HRReportHeroCard } from './hr-report-components/hr-report-hero-card';
import { HRReportSummaryCards } from './hr-report-components/hr-report-summary-cards';
import type { HRReportsPageProps } from './types';

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

export default function HRReportsIndex({ meta, filters, options, headcount, contractExpiry, documentExpiry }: HRReportsPageProps) {
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

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 p-4 sm:p-6">
                <HRReportHeroCard meta={meta} />

                <HRReportFilterCard filters={filters} options={options} onChange={updateFilter} />

                <HRReportSummaryCards headcount={headcount} contractExpiry={contractExpiry} documentExpiry={documentExpiry} />

                <section aria-labelledby="hr-report-headcount-title" className="space-y-3">
                    <div>
                        <h2 id="hr-report-headcount-title" className="text-lg font-semibold tracking-tight">
                            Ringkasan jumlah employee
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Gunakan bagian ini untuk cek sebaran employee aktif berdasarkan tanggal acuan.
                        </p>
                    </div>
                    <div className="grid gap-4 xl:grid-cols-3">
                        <HRReportHeadcountCard
                            title="By Departement"
                            description="Cek unit kerja mana yang paling banyak memegang employee aktif."
                            emptyMessage="Belum ada employee aktif pada tanggal ini. Mulai dari master Departement, lalu isi Employees."
                            report={headcount.byDepartement}
                        />
                        <HRReportHeadcountCard
                            title="By Work Location"
                            description="Cek persebaran employee berdasarkan lokasi kerja utama."
                            emptyMessage="Belum ada employee dengan lokasi kerja aktif. Isi Work Locations dan hubungkan ke Employees."
                            report={headcount.byWorkLocation}
                        />
                        <HRReportHeadcountCard
                            title="Employment Status"
                            description="Cek komposisi status kerja employee pada tanggal acuan."
                            emptyMessage="Belum ada status kerja yang terbaca. Isi Employment Statuses dan profile Employees."
                            report={headcount.byEmploymentStatus}
                        />
                    </div>
                </section>

                <section aria-labelledby="hr-report-risk-title" className="space-y-3">
                    <div>
                        <h2 id="hr-report-risk-title" className="text-lg font-semibold tracking-tight">
                            Masa berlaku yang perlu dipantau
                        </h2>
                        <p className="text-muted-foreground text-sm">
                            Report ini hanya menampilkan metadata aman. Nomor dokumen, DMS reference, token, dan notes internal tidak ditampilkan.
                        </p>
                    </div>
                    <div className="grid gap-4 xl:grid-cols-2">
                        <HRReportExpiryCard
                            title="Contract Expiry"
                            icon={FileText}
                            tone="amber"
                            itemLabel="kontrak"
                            windowLabel="berakhir"
                            emptyMessage="Tidak ada kontrak aktif yang expired atau akan berakhir pada window ini. Jika kosong terus, cek Employee Contracts."
                            report={contractExpiry}
                            typeHeader="Employment Type"
                        />
                        <HRReportExpiryCard
                            title="Document Expiry"
                            icon={FileText}
                            tone="rose"
                            itemLabel="dokumen"
                            windowLabel="kedaluwarsa"
                            emptyMessage="Tidak ada metadata dokumen yang expired atau akan kedaluwarsa pada window ini. Jika perlu, isi Employee Documents."
                            report={documentExpiry}
                            typeHeader="Document Type"
                        />
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
