import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { HeadcountReport, HeadcountReportRow } from '../types';
import { HRReportEmptyState } from './hr-report-empty-state';

export function HRReportHeadcountCard({
    title,
    description,
    emptyMessage,
    report,
}: {
    title: string;
    description: string;
    emptyMessage: string;
    report: HeadcountReport;
}) {
    return (
        <Card className="overflow-hidden">
            <CardHeader>
                <CardTitle>{title}</CardTitle>
                <CardDescription>
                    {description} Total {report.total} per {report.asOf}.
                </CardDescription>
            </CardHeader>
            <CardContent>
                {report.rows.length === 0 ? (
                    <HRReportEmptyState message={emptyMessage} />
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full text-sm">
                            <caption className="sr-only">
                                {title} per {report.asOf}
                            </caption>
                            <thead className="bg-muted/50 text-left">
                                <tr>
                                    <th className="p-3 font-medium" scope="col">
                                        Group
                                    </th>
                                    <th className="p-3 text-right font-medium" scope="col">
                                        Employee
                                    </th>
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
            <td className="p-3 text-right text-lg font-semibold">{row.employeeCount}</td>
        </tr>
    );
}
