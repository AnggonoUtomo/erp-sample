import { Card, CardContent } from '@/components/ui/card';
import type { SchedulerOverview } from '../types';
import { secondsLabel } from '../types';

export function SchedulerOverviewCards({ overview }: { overview: SchedulerOverview }) {
    return (
        <div className="grid gap-4 lg:grid-cols-4">
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Scheduled Tasks</p>
                    <p className="mt-2 text-2xl font-semibold">{overview.tasks}</p>
                    <p className="text-muted-foreground mt-1 text-xs">Task yang terdaftar di Laravel Scheduler.</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Due Next 24h</p>
                    <p className="mt-2 text-2xl font-semibold">{overview.due_24h}</p>
                    <p className="text-muted-foreground mt-1 text-xs">Task yang akan jatuh tempo hari ini.</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Last Heartbeat</p>
                    <p className="mt-2 text-sm font-semibold">{overview.heartbeat.last_run_at ?? 'Belum pernah jalan'}</p>
                    <p className="text-muted-foreground mt-1 text-xs">Age {secondsLabel(overview.heartbeat.age_seconds)}</p>
                    <p className="text-muted-foreground mt-1 text-xs">Diperbarui oleh task heartbeat tiap menit.</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">PHP Binary</p>
                    <p className="mt-2 truncate text-sm font-semibold" title={overview.php_binary}>
                        {overview.php_binary}
                    </p>
                    <p className="text-muted-foreground mt-1 text-xs">Binary yang dipakai untuk cron command.</p>
                </CardContent>
            </Card>
        </div>
    );
}
