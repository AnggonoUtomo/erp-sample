import { Badge } from '@/components/ui/badge';

export function LoginActivityHeader({ total }: { total: number }) {
    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">Login Activity</h1>
                <p className="text-muted-foreground text-sm">Pantau login berhasil, login gagal, logout, IP, dan perangkat user.</p>
            </div>
            <Badge variant="outline" className="w-fit">
                {total} aktivitas
            </Badge>
        </div>
    );
}
