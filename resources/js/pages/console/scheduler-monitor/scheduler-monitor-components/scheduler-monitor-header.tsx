import { Badge } from '@/components/ui/badge';
import type { SchedulerOverview } from '../types';
import { heartbeatClasses, heartbeatLabels } from '../types';

export function SchedulerMonitorHeader({ overview }: { overview: SchedulerOverview }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Scheduler Monitor</h1>
                <p className="text-muted-foreground text-sm">Pantau task Laravel Scheduler, status cron server, dan jadwal eksekusi otomatis aplikasi.</p>
            </div>
            <div className="flex flex-wrap gap-2">
                <Badge variant="outline">{overview.timezone}</Badge>
                <Badge className={heartbeatClasses[overview.heartbeat.status]}>{heartbeatLabels[overview.heartbeat.status]}</Badge>
            </div>
        </div>
    );
}
