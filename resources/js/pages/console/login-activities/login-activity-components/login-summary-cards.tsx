import { Card, CardContent } from '@/components/ui/card';

type Summary = {
    total: number;
    success: number;
    failed: number;
    today: number;
};

export function LoginSummaryCards({ summary }: { summary: Summary }) {
    return (
        <div className="grid gap-4 md:grid-cols-4">
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Total</p>
                    <p className="mt-2 text-2xl font-semibold">{summary.total}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Success</p>
                    <p className="mt-2 text-2xl font-semibold">{summary.success}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Failed</p>
                    <p className="mt-2 text-2xl font-semibold">{summary.failed}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Today</p>
                    <p className="mt-2 text-2xl font-semibold">{summary.today}</p>
                </CardContent>
            </Card>
        </div>
    );
}
