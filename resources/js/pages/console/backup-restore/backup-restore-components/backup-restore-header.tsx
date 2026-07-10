import { Badge } from '@/components/ui/badge';

export function BackupRestoreHeader() {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Backup & Restore</h1>
                <p className="text-muted-foreground text-sm">Export dan restore konfigurasi aplikasi dalam format JSON.</p>
            </div>
            <Badge variant="outline" className="w-fit">
                Readable JSON
            </Badge>
        </div>
    );
}
