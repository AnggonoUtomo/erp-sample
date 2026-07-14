import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Archive, ArrowLeft, CalendarDays, CheckCircle2, ClipboardList, Clock3 } from 'lucide-react';
import { OnboardingSummaryCards } from './onboarding-components/onboarding-summary-cards';
import type { OnboardingDetail } from './types';

type Props = { onboarding: OnboardingDetail; businessDate: string };

export default function OnboardingShow({ onboarding, businessDate }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'HR', href: '/hr/dashboard' },
        { title: 'Employee Onboardings', href: '/hr/onboardings' },
        { title: onboarding.employee?.display_name ?? `Onboarding #${onboarding.id}`, href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Onboarding ${onboarding.employee?.display_name ?? `#${onboarding.id}`}`} />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <Button asChild variant="ghost" size="sm" className="mb-2 -ml-3">
                            <Link href={route('hr.onboardings.index')}>
                                <ArrowLeft className="size-4" /> Kembali
                            </Link>
                        </Button>
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {onboarding.employee?.display_name ?? 'Employee tidak tersedia'}
                            </h1>
                            <Badge variant="secondary">{onboarding.status}</Badge>
                            {onboarding.archived && (
                                <Badge variant="outline">
                                    <Archive className="mr-1 size-3" /> Diarsipkan
                                </Badge>
                            )}
                        </div>
                        <p className="text-muted-foreground mt-1 text-sm">
                            {onboarding.employee?.employee_number ?? 'Tanpa nomor employee'} ·{' '}
                            {onboarding.template?.name ?? 'Template tidak tersedia'}
                        </p>
                    </div>
                    <div className="text-muted-foreground flex items-center gap-2 text-sm">
                        <CalendarDays className="size-4" /> Business date: {businessDate}
                    </div>
                </div>

                <OnboardingSummaryCards progress={onboarding.progress} />

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2 text-lg">
                                <ClipboardList className="size-5" /> Checklist
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {onboarding.tasks.length === 0 && (
                                <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">
                                    Checklist onboarding ini kosong.
                                </div>
                            )}
                            {onboarding.tasks.map((task) => (
                                <div key={task.id} className="flex gap-3 rounded-lg border p-4">
                                    {task.status === 'COMPLETED' || task.status === 'SKIPPED' ? (
                                        <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-emerald-600" />
                                    ) : (
                                        <Clock3 className={`mt-0.5 size-5 shrink-0 ${task.overdue ? 'text-destructive' : 'text-muted-foreground'}`} />
                                    )}
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="font-medium">{task.title}</span>
                                            <Badge variant={task.required ? 'default' : 'outline'}>{task.required ? 'Wajib' : 'Opsional'}</Badge>
                                            <Badge variant="secondary">{task.status}</Badge>
                                            {task.overdue && <Badge variant="destructive">Terlambat</Badge>}
                                        </div>
                                        {task.description && <p className="text-muted-foreground mt-1 text-sm">{task.description}</p>}
                                        <p className="text-muted-foreground mt-2 text-xs">
                                            {task.category} · Jatuh tempo {task.due_date}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>

                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle className="text-lg">Konteks onboarding</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm">
                            <Detail label="Tanggal mulai" value={onboarding.start_date} />
                            <Detail label="Owner" value={onboarding.owner?.name ?? 'Tidak tersedia'} />
                            <Detail label="Kontrak" value={onboarding.contract?.contract_number ?? 'Tanpa kontrak'} />
                            <Detail label="Template snapshot" value={onboarding.template?.name ?? 'Tidak tersedia'} />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

function Detail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <div className="text-muted-foreground text-xs">{label}</div>
            <div className="mt-1 font-medium">{value}</div>
        </div>
    );
}
