import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, CheckCheck, ExternalLink } from 'lucide-react';

function moduleLabel(module: string) {
    return module
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
}

export function ActivityCenterDropdown() {
    const { activity_center: activityCenter, auth } = usePage<SharedData>().props;
    const canViewActivity = Boolean(auth.permissions['activity-center.view'] || auth.super);
    const unreadCount = activityCenter?.unread_count ?? 0;
    const items = activityCenter?.items ?? [];

    if (!canViewActivity) {
        return null;
    }

    const markAsRead = () => {
        router.post(route('activity-center.read'), {}, { preserveScroll: true });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button type="button" variant="ghost" size="icon" className="relative size-9 rounded-lg">
                    <Bell className="size-4" />
                    {unreadCount > 0 ? (
                        <span className="bg-primary text-primary-foreground absolute -top-1 -right-1 flex min-w-5 items-center justify-center rounded-full px-1.5 text-[10px] font-semibold">
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </span>
                    ) : null}
                    <span className="sr-only">Activity center</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-[360px] overflow-hidden rounded-xl p-0">
                <div className="flex items-start justify-between gap-3 border-b p-4">
                    <div>
                        <p className="text-sm font-semibold">Activity Center</p>
                        <p className="text-muted-foreground mt-1 text-xs">
                            {unreadCount > 0 ? `${unreadCount} aktivitas belum dibaca` : 'Semua aktivitas terbaru sudah dibaca'}
                        </p>
                    </div>
                    <Button type="button" variant="ghost" size="sm" disabled={unreadCount === 0} onClick={markAsRead} className="h-8 gap-1.5">
                        <CheckCheck className="size-4" />
                        Read
                    </Button>
                </div>

                <div className="max-h-[420px] overflow-y-auto p-2">
                    {items.length > 0 ? (
                        <div className="space-y-1">
                            {items.map((item) => (
                                <div key={item.id} className="hover:bg-muted/70 flex gap-3 rounded-lg p-3 transition-colors">
                                    <span
                                        className={
                                            item.unread
                                                ? 'bg-primary mt-1 size-2 shrink-0 rounded-full'
                                                : 'bg-muted-foreground/30 mt-1 size-2 shrink-0 rounded-full'
                                        }
                                    />
                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-start justify-between gap-2">
                                            <p className="truncate text-sm font-medium">{item.description ?? item.event}</p>
                                            <Badge variant="secondary" className="shrink-0 text-[10px]">
                                                {moduleLabel(item.module)}
                                            </Badge>
                                        </div>
                                        <p className="text-muted-foreground mt-1 line-clamp-1 text-xs">
                                            {item.actor?.name ?? 'System'} • {item.created_at_human ?? item.created_at}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-muted-foreground flex min-h-32 items-center justify-center rounded-lg border border-dashed p-4 text-center text-sm">
                            Belum ada activity yang bisa ditampilkan.
                        </div>
                    )}
                </div>

                <div className="border-t p-2">
                    <Button asChild variant="ghost" className="h-9 w-full justify-between">
                        <Link href={route('audit-logs.index')}>
                            Buka Audit Logs
                            <ExternalLink className="size-4" />
                        </Link>
                    </Button>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
