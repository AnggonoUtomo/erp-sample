import { Card, CardContent } from '@/components/ui/card';
import { Building2, CalendarClock, FileClock, MapPin, UsersRound } from 'lucide-react';
import type { ExpiryReport, HRReportsPageProps } from '../types';

export function HRReportSummaryCards({
    headcount,
    contractExpiry,
    documentExpiry,
}: {
    headcount: HRReportsPageProps['headcount'];
    contractExpiry: ExpiryReport;
    documentExpiry: ExpiryReport;
}) {
    return (
        <section aria-label="Ringkasan HR Reports" className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <SummaryCard title="Departement" value={headcount.byDepartement.total} helper="employee aktif" icon={Building2} />
            <SummaryCard title="Work Location" value={headcount.byWorkLocation.total} helper="employee aktif" icon={MapPin} />
            <SummaryCard title="Status kerja" value={headcount.byEmploymentStatus.total} helper="employee aktif" icon={UsersRound} />
            <SummaryCard title="Kontrak" value={contractExpiry.total} helper="perlu dipantau" icon={CalendarClock} />
            <SummaryCard title="Dokumen" value={documentExpiry.total} helper="perlu dipantau" icon={FileClock} />
        </section>
    );
}

function SummaryCard({
    title,
    value,
    helper,
    icon: Icon,
}: {
    title: string;
    value: number;
    helper: string;
    icon: React.ComponentType<{ className?: string }>;
}) {
    return (
        <Card>
            <CardContent className="flex items-center gap-3 p-4">
                <div className="bg-primary/10 text-primary flex size-10 shrink-0 items-center justify-center rounded-xl">
                    <Icon className="size-5" aria-hidden="true" />
                </div>
                <div className="min-w-0">
                    <p className="text-muted-foreground truncate text-sm">{title}</p>
                    <p className="text-2xl leading-tight font-semibold">{value}</p>
                    <p className="text-muted-foreground text-xs">{helper}</p>
                </div>
            </CardContent>
        </Card>
    );
}
