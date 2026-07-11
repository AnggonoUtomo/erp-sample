import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { router } from '@inertiajs/react';
import { CalendarClock, Clock, Play, RefreshCcw } from 'lucide-react';
import type { ScheduledEvent } from '../types';

type Props = {
    events: ScheduledEvent[];
    canManage: boolean;
    onRun: () => void;
};

export function ScheduledTaskCard({ events, canManage, onRun }: Props) {
    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="border-b">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <span className="dashboard-icon icon-tone-indigo flex size-10 items-center justify-center rounded-md">
                                <CalendarClock className="size-5" />
                            </span>
                            Scheduled Tasks
                        </CardTitle>
                        <CardDescription>Daftar task yang terdaftar di Laravel Scheduler.</CardDescription>
                        <p className="text-muted-foreground mt-2 max-w-2xl text-xs leading-relaxed">
                            Task di tabel ini berasal dari `php artisan schedule:list`. Kolom expression menunjukkan pola cron, task menunjukkan
                            command/closure yang berjalan, dan next due memperlihatkan estimasi eksekusi berikutnya.
                        </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Button variant="outline" onClick={() => router.reload()}>
                            <RefreshCcw className="size-4" />
                            Refresh
                        </Button>
                        <Button disabled={!canManage} onClick={onRun}>
                            <Play className="size-4" />
                            Run Due Tasks
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-0">
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[860px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left">
                            <tr>
                                <th className="w-[18%] px-4 py-3 font-semibold">Expression</th>
                                <th className="w-[38%] px-4 py-3 font-semibold">Task</th>
                                <th className="w-[16%] px-4 py-3 font-semibold">Timezone</th>
                                <th className="w-[20%] px-4 py-3 font-semibold">Next Due</th>
                                <th className="w-[8%] px-4 py-3 text-right font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {events.length ? (
                                events.map((event, index) => (
                                    <tr key={`${event.expression}-${event.command}-${index}`} className="border-t align-top">
                                        <td className="px-4 py-3">
                                            <code className="bg-muted rounded px-2 py-1 text-xs">{event.expression}</code>
                                        </td>
                                        <td className="px-4 py-3">
                                            <p className="truncate font-medium" title={event.command}>
                                                {event.description}
                                            </p>
                                            <p className="text-muted-foreground mt-1 truncate text-xs">{event.command}</p>
                                        </td>
                                        <td className="px-4 py-3">{event.timezone}</td>
                                        <td className="px-4 py-3">
                                            <span className="flex items-center gap-2">
                                                <Clock className="text-muted-foreground size-4" />
                                                <span>
                                                    <span className="block">{event.next_due ?? '-'}</span>
                                                    <span className="text-muted-foreground text-xs">{event.next_due_human}</span>
                                                </span>
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            <Badge variant={event.is_due_soon ? 'default' : 'secondary'}>
                                                {event.is_due_soon ? 'Soon' : 'Later'}
                                            </Badge>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td className="text-muted-foreground px-4 py-10 text-center" colSpan={5}>
                                        Belum ada scheduled task yang terdaftar.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    );
}
