import { Card, CardContent } from '@/components/ui/card';
import type { OffboardingProgress } from '../types';

export function OffboardingSummaryCards({ progress }: { progress: OffboardingProgress }) {
    const summaries = [
        ['Progress', `${progress.percentage}%`],
        ['Selesai', progress.completed],
        ['Dilewati', progress.skipped],
        ['Wajib tersisa', progress.required_incomplete],
        ['Opsional tersisa', progress.optional_incomplete],
        ['Terlambat', progress.overdue],
    ];

    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            {summaries.map(([label, value]) => (
                <Card key={label}>
                    <CardContent className="p-4">
                        <div className="text-muted-foreground text-xs">{label}</div>
                        <div className="mt-1 text-xl font-semibold">{value}</div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
