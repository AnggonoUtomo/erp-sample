import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Activity, Info, ServerCog, Terminal } from 'lucide-react';
import type { SchedulerOverview } from '../types';
import { heartbeatClasses, heartbeatDescriptions, heartbeatLabels, secondsLabel } from '../types';

export function SchedulerSidePanels({ overview }: { overview: SchedulerOverview }) {
    return (
        <div className="space-y-6">
            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-emerald flex size-8 items-center justify-center rounded-md">
                            <Activity className="size-4" />
                        </span>
                        Scheduler Health
                    </CardTitle>
                    <CardDescription>Status ini berasal dari heartbeat tiap menit.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4 p-5">
                    <div className="rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Status</p>
                        <div className="mt-2 flex items-center gap-2">
                            <Badge className={heartbeatClasses[overview.heartbeat.status]}>{heartbeatLabels[overview.heartbeat.status]}</Badge>
                            <span className="text-muted-foreground text-xs">Age {secondsLabel(overview.heartbeat.age_seconds)}</span>
                        </div>
                        <p className="text-muted-foreground mt-3 text-xs leading-relaxed">{heartbeatDescriptions[overview.heartbeat.status]}</p>
                    </div>
                    <div className="grid gap-2 text-xs">
                        <div className="flex items-start gap-2 rounded-lg border p-3">
                            <Badge className="mt-0.5 bg-emerald-600 text-white">Fresh</Badge>
                            <span className="text-muted-foreground leading-relaxed">Cron berjalan normal dalam 2 menit terakhir.</span>
                        </div>
                        <div className="flex items-start gap-2 rounded-lg border p-3">
                            <Badge className="mt-0.5 bg-amber-500 text-white">Stale</Badge>
                            <span className="text-muted-foreground leading-relaxed">Heartbeat telat 2-10 menit, biasanya cron lambat atau worker sibuk.</span>
                        </div>
                        <div className="flex items-start gap-2 rounded-lg border p-3">
                            <Badge className="mt-0.5 bg-red-600 text-white">Down</Badge>
                            <span className="text-muted-foreground leading-relaxed">Heartbeat lebih dari 10 menit, cron kemungkinan mati.</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-indigo flex size-8 items-center justify-center rounded-md">
                            <Info className="size-4" />
                        </span>
                        Fungsi Panel
                    </CardTitle>
                    <CardDescription>Kapan panel ini dipakai dan apa yang perlu dicek.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-3 p-5 text-sm leading-relaxed">
                    <div className="rounded-lg border p-4">
                        <p className="font-medium">Monitoring otomatis</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                            Memastikan scheduler server aktif sehingga email queue, cleanup, laporan berkala, dan task periodik lain bisa berjalan tanpa klik manual.
                        </p>
                    </div>
                    <div className="rounded-lg border p-4">
                        <p className="font-medium">Diagnosa cepat</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                            Jika task tidak berjalan, cek status heartbeat, cron command, timezone, dan next due task dari panel ini.
                        </p>
                    </div>
                    <div className="rounded-lg border p-4">
                        <p className="font-medium">Run Due Tasks</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                            Tombol ini menjalankan `php artisan schedule:run` sekali. Hanya task yang sudah due yang akan dieksekusi.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-md">
                            <Terminal className="size-4" />
                        </span>
                        Cron Command
                    </CardTitle>
                    <CardDescription>Command yang perlu dipasang di server production.</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4 p-5">
                    <div className="bg-muted/70 overflow-x-auto rounded-lg border p-4">
                        <code className="text-xs whitespace-pre">{overview.cron_command}</code>
                    </div>
                    <div className="bg-muted/50 rounded-lg border p-4 text-xs leading-relaxed">
                        Pasang command ini di crontab server agar Laravel Scheduler memanggil `schedule:run` setiap menit. Di Windows lokal, konsepnya bisa diganti Task Scheduler atau menjalankan command manual saat development.
                    </div>
                    <div className="rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Artisan Path</p>
                        <p className="mt-2 break-all text-sm font-medium">{overview.artisan_path}</p>
                    </div>
                </CardContent>
            </Card>

            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-amber flex size-8 items-center justify-center rounded-md">
                            <ServerCog className="size-4" />
                        </span>
                        Catatan Operasional
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 p-5 text-sm leading-relaxed">
                    <p>Panel ini tidak menggantikan cron server. Scheduler tetap butuh proses eksternal yang memanggil Laravel tiap menit.</p>
                    <div className="bg-muted/50 rounded-lg border p-4 text-xs leading-relaxed">
                        Setelah deploy production, cek panel ini beberapa menit kemudian. Status ideal adalah Fresh dan Last Heartbeat terus bergerak.
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}
