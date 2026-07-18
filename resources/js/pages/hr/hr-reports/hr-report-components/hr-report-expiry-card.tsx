import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { ExpiryReport, ExpiryReportRow } from '../types';
import { HRReportEmptyState } from './hr-report-empty-state';

type Tone = 'amber' | 'rose';

const iconTone: Record<Tone, string> = {
    amber: 'text-amber-500',
    rose: 'text-rose-500',
};

export function HRReportExpiryCard({
    title,
    icon: Icon,
    tone,
    itemLabel,
    windowLabel,
    emptyMessage,
    report,
    typeHeader,
}: {
    title: string;
    icon: React.ComponentType<{ className?: string }>;
    tone: Tone;
    itemLabel: string;
    windowLabel: string;
    emptyMessage: string;
    report: ExpiryReport;
    typeHeader: string;
}) {
    return (
        <Card className="overflow-hidden">
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Icon className={`size-5 ${iconTone[tone]}`} aria-hidden="true" />
                    {title}
                </CardTitle>
                <CardDescription>
                    {report.total} {itemLabel} {windowLabel} dalam {report.withinDays} hari dari {report.asOf}.
                </CardDescription>
            </CardHeader>
            <CardContent>
                {report.rows.length === 0 ? (
                    <HRReportEmptyState message={emptyMessage} />
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full min-w-[620px] text-sm">
                            <caption className="sr-only">
                                {title} dari {report.asOf} selama {report.withinDays} hari
                            </caption>
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3 font-medium" scope="col">
                                        Employee
                                    </th>
                                    <th className="p-3 font-medium" scope="col">
                                        {typeHeader}
                                    </th>
                                    <th className="p-3 font-medium" scope="col">
                                        Expiry Date
                                    </th>
                                    <th className="p-3 text-right font-medium" scope="col">
                                        Remaining
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {report.rows.map((row) => (
                                    <ExpiryRow key={`${title}-${row.employeeId}-${row.expiresAt}-${row.typeLabel}`} row={row} />
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function ExpiryRow({ row }: { row: ExpiryReportRow }) {
    return (
        <tr className="border-t align-top">
            <td className="p-3">
                <p className="font-medium">{row.employeeName}</p>
                <p className="text-muted-foreground text-xs">{row.employeeNumber}</p>
            </td>
            <td className="p-3">{row.typeLabel}</td>
            <td className="p-3">
                <Badge variant={row.state === 'EXPIRED' ? 'destructive' : 'secondary'}>{row.state === 'EXPIRED' ? 'Expired' : 'Expiring'}</Badge>
                <span className="ml-2 whitespace-nowrap">{row.expiresAt}</span>
            </td>
            <td className="p-3 text-right font-semibold">
                <span className={row.daysRemaining < 0 ? 'text-destructive' : ''}>{row.daysRemaining} hari</span>
            </td>
        </tr>
    );
}
