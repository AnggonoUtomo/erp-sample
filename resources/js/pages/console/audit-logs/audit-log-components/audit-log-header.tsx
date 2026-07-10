import { Badge } from '@/components/ui/badge';

export function AuditLogHeader({ total }: { total: number }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Audit Logs</h1>
                <p className="text-muted-foreground text-sm">Lacak aktivitas penting pada user, role, permission, dan system setting.</p>
            </div>
            <Badge variant="outline" className="w-fit">
                {total} log
            </Badge>
        </div>
    );
}
