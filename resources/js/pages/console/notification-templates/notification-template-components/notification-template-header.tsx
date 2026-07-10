import { Badge } from '@/components/ui/badge';

export function NotificationTemplateHeader({ count }: { count: number }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Notification Templates</h1>
                <p className="text-muted-foreground text-sm">Kelola teks email otomatis tanpa menyentuh kode notifikasi.</p>
            </div>
            <Badge variant="outline" className="w-fit">
                {count} template
            </Badge>
        </div>
    );
}
