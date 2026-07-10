import { Card, CardContent } from '@/components/ui/card';
import type { QueueOverview } from '../types';

function valueLabel(value: number | null) {
    return value === null ? 'N/A' : value;
}

export function QueueOverviewCards({ overview }: { overview: QueueOverview }) {
    return (
        <div className="grid gap-4 lg:grid-cols-4">
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Pending Jobs</p>
                    <p className="mt-2 text-2xl font-semibold">{valueLabel(overview.pending)}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Reserved Jobs</p>
                    <p className="mt-2 text-2xl font-semibold">{valueLabel(overview.reserved)}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Failed Jobs</p>
                    <p className="mt-2 text-2xl font-semibold">{valueLabel(overview.failed)}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Tables</p>
                    <p className="mt-2 text-sm font-semibold">{overview.jobs_table}</p>
                    <p className="text-muted-foreground mt-1 text-xs">{overview.failed_table}</p>
                </CardContent>
            </Card>
        </div>
    );
}
