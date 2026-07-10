import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle2, Server } from 'lucide-react';
import type { EnvironmentInfo } from '../types';
import { healthIconClass } from '../utils';

export function EnvironmentInfoPanel({ environmentInfo }: { environmentInfo: EnvironmentInfo }) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-sky flex size-10 items-center justify-center rounded-md">
                        <Server className="size-5" />
                    </span>
                    Environment Info
                </CardTitle>
                <CardDescription>Informasi environment yang aman dibaca untuk diagnosis dan deployment.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6 p-5 sm:p-6">
                <div className="grid gap-4 lg:grid-cols-3">
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Mode</p>
                        <p className="mt-2 text-2xl font-semibold">{environmentInfo.summary.mode}</p>
                    </div>
                    <div className="bg-background/60 rounded-lg border p-4">
                        <p className="text-muted-foreground text-xs">Generated At</p>
                        <p className="mt-2 text-lg font-semibold">{environmentInfo.summary.generated_at}</p>
                    </div>
                    <div className="bg-muted/40 rounded-lg border p-4">
                        <p className="text-sm font-medium">Catatan Keamanan</p>
                        <p className="text-muted-foreground mt-1 text-xs leading-relaxed">{environmentInfo.summary.notice}</p>
                    </div>
                </div>

                <div className="grid gap-4 xl:grid-cols-2">
                    {environmentInfo.groups.map((group) => (
                        <div key={group.title} className="bg-background/60 rounded-lg border">
                            <div className="border-b p-4">
                                <p className="font-medium">{group.title}</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-relaxed">{group.description}</p>
                            </div>
                            <div className="divide-y">
                                {group.items.map((item) => (
                                    <div key={`${group.title}-${item.label}`} className="grid gap-2 p-4 sm:grid-cols-[180px_minmax(0,1fr)]">
                                        <div className="flex items-center gap-2">
                                            {item.status ? <CheckCircle2 className={`size-4 ${healthIconClass(item.status)}`} /> : null}
                                            <p className="text-muted-foreground text-sm">{item.label}</p>
                                        </div>
                                        <p className="text-sm font-medium break-words">{String(item.value ?? '-')}</p>
                                    </div>
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
