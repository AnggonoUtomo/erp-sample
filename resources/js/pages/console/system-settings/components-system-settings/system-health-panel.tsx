import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle2, HeartPulse } from 'lucide-react';
import type { SystemHealth } from '../types';
import { healthBadgeVariant, healthIconClass, healthStatusLabel } from '../utils';

export function SystemHealthPanel({ systemHealth }: { systemHealth: SystemHealth }) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-emerald flex size-10 items-center justify-center rounded-md">
                        <HeartPulse className="size-5" />
                    </span>
                    System Health
                </CardTitle>
                <CardDescription>Ringkasan kesehatan runtime aplikasi dan konfigurasi operasional.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6 p-5 sm:p-6">
                <div className="grid gap-4 lg:grid-cols-4">
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Overall</p>
                        <div className="mt-2 flex items-center gap-2">
                            <CheckCircle2 className={`size-5 ${healthIconClass(systemHealth.summary.status)}`} />
                            <p className="text-2xl font-semibold">{healthStatusLabel(systemHealth.summary.status)}</p>
                        </div>
                    </div>
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Healthy</p>
                        <p className="mt-2 text-2xl font-semibold">{systemHealth.summary.ok}</p>
                    </div>
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Warning</p>
                        <p className="mt-2 text-2xl font-semibold">{systemHealth.summary.warning}</p>
                    </div>
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Error</p>
                        <p className="mt-2 text-2xl font-semibold">{systemHealth.summary.error}</p>
                    </div>
                </div>

                <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_280px]">
                    <div className="space-y-3">
                        {systemHealth.checks.map((check) => (
                            <div
                                key={check.name}
                                className="bg-background/60 flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div className="min-w-0 space-y-1">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <CheckCircle2 className={`size-4 ${healthIconClass(check.status)}`} />
                                        <p className="font-medium">{check.name}</p>
                                        <Badge variant={healthBadgeVariant(check.status)}>{healthStatusLabel(check.status)}</Badge>
                                    </div>
                                    <p className="text-muted-foreground text-sm leading-relaxed">{check.description}</p>
                                    {check.message !== 'OK' ? <p className="text-muted-foreground text-xs leading-relaxed">{check.message}</p> : null}
                                </div>
                                <div className="min-w-0 text-left sm:text-right">
                                    <p className="text-sm font-semibold break-words">{String(check.value ?? '-')}</p>
                                    {check.meta ? <p className="text-muted-foreground mt-1 text-xs break-words">{check.meta}</p> : null}
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="space-y-4">
                        <div className="rounded-lg border border-dashed p-4">
                            <p className="text-sm font-medium">Runtime Info</p>
                            <p className="text-muted-foreground mt-1 text-xs leading-relaxed">Dibaca langsung dari konfigurasi aktif. Secret tidak ditampilkan.</p>
                        </div>
                        <div className="grid gap-2">
                            {Object.entries(systemHealth.runtime).map(([key, value]) => (
                                <div key={key} className="bg-background/60 rounded-lg border p-3">
                                    <p className="text-muted-foreground text-xs capitalize">{key.replaceAll('_', ' ')}</p>
                                    <p className="mt-1 text-sm font-medium break-words">{String(value ?? '-')}</p>
                                </div>
                            ))}
                        </div>
                        <div className="bg-muted/50 rounded-lg border p-4 text-xs leading-relaxed">
                            <p className="font-medium">Terakhir Dicek</p>
                            <p className="text-muted-foreground mt-1">{systemHealth.summary.checked_at}</p>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
