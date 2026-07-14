import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Archive, ArrowLeft, CalendarDays, CheckCircle2, ClipboardList, Clock3 } from 'lucide-react';
import { canActivateOnboarding, OnboardingActivationDialog } from './onboarding-components/onboarding-activation-dialog';
import {
    canCancelOnboarding,
    CancelOnboardingDialog,
    canCompleteOnboarding,
    CompleteOnboardingDialog,
} from './onboarding-components/onboarding-lifecycle-dialogs';
import { onboardingStatusLabel, onboardingTaskStatusLabel } from './onboarding-components/onboarding-presenters';
import { OnboardingSummaryCards } from './onboarding-components/onboarding-summary-cards';
import { OnboardingTaskControls } from './onboarding-components/onboarding-task-controls';
import type { OnboardingDetail } from './types';

type Props = { onboarding: OnboardingDetail; businessDate: string; assigneeOptions: { id: number; name: string }[] };

export default function OnboardingShow({ onboarding, businessDate, assigneeOptions }: Props) {
    const { canAny } = usePermission();
    const showActivation = canActivateOnboarding(onboarding.status, onboarding.archived, canAny(['onboardings.activate', 'onboardings.manage']));
    const canUpdateTasks = canAny(['onboardings.task-update', 'onboardings.manage']);
    const canSkipRequired = canAny(['onboardings.task-skip-required', 'onboardings.manage']);
    const showComplete = canCompleteOnboarding(
        onboarding.status,
        onboarding.archived,
        onboarding.progress.required_incomplete,
        canAny(['onboardings.complete', 'onboardings.manage']),
    );
    const showCancel = canCancelOnboarding(onboarding.status, onboarding.archived, canAny(['onboardings.cancel', 'onboardings.manage']));
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
                            <Badge variant="secondary">{onboardingStatusLabel(onboarding.status)}</Badge>
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
                    <div className="flex w-full flex-col items-start gap-3 sm:w-auto sm:items-end">
                        <div className="text-muted-foreground flex items-center gap-2 text-sm">
                            <CalendarDays className="size-4" /> Business date: {businessDate}
                        </div>
                        <div className="flex w-full flex-wrap gap-2 sm:w-auto sm:justify-end">
                            {showActivation && <OnboardingActivationDialog onboardingId={onboarding.id} />}
                            {showComplete && <CompleteOnboardingDialog onboardingId={onboarding.id} />}
                            {showCancel && <CancelOnboardingDialog onboardingId={onboarding.id} />}
                        </div>
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
                                            <Badge variant="secondary">{onboardingTaskStatusLabel(task.status)}</Badge>
                                            {task.overdue && <Badge variant="destructive">Terlambat</Badge>}
                                        </div>
                                        {task.description && <p className="text-muted-foreground mt-1 text-sm">{task.description}</p>}
                                        <p className="text-muted-foreground mt-2 text-xs">
                                            {task.category} · Jatuh tempo {task.due_date}
                                        </p>
                                        <p className="text-muted-foreground mt-1 text-xs">Assignee: {task.assignee?.name ?? 'Belum ditugaskan'}</p>
                                        {task.completed_by && (
                                            <div className="mt-2 rounded-md bg-emerald-50 p-2 text-xs text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">
                                                Diselesaikan oleh {task.completed_by.name}
                                                {task.completed_at ? ` · ${new Date(task.completed_at).toLocaleString('id-ID')}` : ''}
                                                {task.completion_note && <p className="mt-1">{task.completion_note}</p>}
                                            </div>
                                        )}
                                        {task.skipped_by && (
                                            <div className="mt-2 rounded-md bg-amber-50 p-2 text-xs text-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                                                Di-skip oleh {task.skipped_by.name}
                                                {task.skipped_at ? ` · ${new Date(task.skipped_at).toLocaleString('id-ID')}` : ''}
                                                {task.skip_reason && <p className="mt-1">{task.skip_reason}</p>}
                                            </div>
                                        )}
                                        {task.reopened_by && (
                                            <div className="text-muted-foreground bg-muted mt-2 rounded-md p-2 text-xs">
                                                Dibuka kembali oleh {task.reopened_by.name}
                                                {task.reopened_at ? ` · ${new Date(task.reopened_at).toLocaleString('id-ID')}` : ''}
                                                {task.reopen_reason && <p className="mt-1">{task.reopen_reason}</p>}
                                            </div>
                                        )}
                                        <OnboardingTaskControls
                                            onboardingId={onboarding.id}
                                            onboardingStatus={onboarding.status}
                                            archived={onboarding.archived}
                                            task={task}
                                            assigneeOptions={assigneeOptions}
                                            hasPermission={canUpdateTasks}
                                            canSkipRequired={canSkipRequired}
                                        />
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
                            {onboarding.completed_by && (
                                <Detail
                                    label="Diselesaikan oleh"
                                    value={`${onboarding.completed_by.name}${onboarding.completed_at ? ` · ${new Date(onboarding.completed_at).toLocaleString('id-ID')}` : ''}`}
                                />
                            )}
                            {onboarding.cancelled_by && (
                                <Detail
                                    label="Dibatalkan oleh"
                                    value={`${onboarding.cancelled_by.name}${onboarding.cancelled_at ? ` · ${new Date(onboarding.cancelled_at).toLocaleString('id-ID')}` : ''}`}
                                />
                            )}
                            {onboarding.cancel_reason && <Detail label="Alasan pembatalan" value={onboarding.cancel_reason} />}
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
