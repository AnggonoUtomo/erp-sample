import { Badge } from '@/components/ui/badge';

export function QueueMonitorHeader({ connection }: { connection: string }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Queue Monitor</h1>
                <p className="text-muted-foreground text-sm">Pantau pending jobs, failed jobs, dan aksi retry untuk queue aplikasi.</p>
            </div>
            <Badge variant="outline" className="w-fit">
                {connection} connection
            </Badge>
        </div>
    );
}
