import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Archive, ArrowLeft, CalendarDays } from 'lucide-react';
import { canActivateOffboarding, OffboardingActivationDialog } from './offboarding-components/offboarding-activation-dialog';
import { canCancelOffboarding, OffboardingCancelDialog } from './offboarding-components/offboarding-cancel-dialog';
import { canFinalizeOffboarding, OffboardingFinalizeDialog } from './offboarding-components/offboarding-finalize-dialog';
import { offboardingExitTypeLabel, offboardingStatusLabel } from './offboarding-components/offboarding-presenters';
import { canMarkOffboardingReady, OffboardingReadyDialog } from './offboarding-components/offboarding-ready-dialog';
import { OffboardingSummaryCards } from './offboarding-components/offboarding-summary-cards';
import { OffboardingTaskList } from './offboarding-components/offboarding-task-list';
import type { OffboardingDetail } from './types';

type Props = {
    offboarding: OffboardingDetail;
    businessDate: string;
    assigneeOptions: { id: number; name: string }[];
};

export default function OffboardingShow({ offboarding, businessDate, assigneeOptions }: Props) {
    const { canAny } = usePermission();
    const canUpdateTasks = canAny(['offboardings.task-update', 'offboardings.manage']);
    const canSkipRequired = canAny(['offboardings.task-skip-required', 'offboardings.manage']);
    const showActivation = canActivateOffboarding(offboarding.status, offboarding.archived, canAny(['offboardings.activate', 'offboardings.manage']));
    const showReady = canMarkOffboardingReady(
        offboarding.status,
        offboarding.archived,
        offboarding.progress.required_incomplete,
        canAny(['offboardings.mark-ready', 'offboardings.manage']),
    );
    const showCancel = canCancelOffboarding(offboarding.status, offboarding.archived, canAny(['offboardings.cancel', 'offboardings.manage']));
    const showFinalize = canFinalizeOffboarding(
        offboarding.status,
        offboarding.archived,
        businessDate,
        offboarding.exit_date,
        canAny(['offboardings.finalize', 'offboardings.manage']),
    );
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'HR', href: '/hr/dashboard' },
        { title: 'Employee Offboardings', href: '/hr/offboardings' },
        { title: offboarding.employee?.display_name ?? `Offboarding #${offboarding.id}`, href: '#' },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Offboarding ${offboarding.employee?.display_name ?? `#${offboarding.id}`}`} />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <Button asChild variant="ghost" size="sm" className="mb-2 -ml-3">
                            <Link href={route('hr.offboardings.index')}>
                                <ArrowLeft className="size-4" /> Kembali
                            </Link>
                        </Button>
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="text-2xl font-semibold tracking-tight">
                                {offboarding.employee?.display_name ?? 'Employee tidak tersedia'}
                            </h1>
                            <Badge variant="secondary">{offboardingStatusLabel(offboarding.status)}</Badge>
                            {offboarding.archived && (
                                <Badge variant="outline">
                                    <Archive className="mr-1 size-3" /> Diarsipkan
                                </Badge>
                            )}
                        </div>
                        <p className="text-muted-foreground mt-1 text-sm">
                            {offboarding.employee?.employee_number ?? 'Tanpa nomor employee'} ·{' '}
                            {offboarding.template?.name ?? 'Template tidak tersedia'}
                        </p>
                    </div>
                    <div className="flex w-full flex-col items-start gap-3 sm:w-auto sm:items-end">
                        <div className="text-muted-foreground flex items-center gap-2 text-sm">
                            <CalendarDays className="size-4" /> Business date: {businessDate}
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {showActivation && <OffboardingActivationDialog offboardingId={offboarding.id} />}
                            {showReady && <OffboardingReadyDialog offboardingId={offboarding.id} />}
                            {showFinalize && (
                                <OffboardingFinalizeDialog
                                    offboardingId={offboarding.id}
                                    businessDate={businessDate}
                                    exitDate={offboarding.exit_date}
                                />
                            )}
                            {showCancel && <OffboardingCancelDialog offboardingId={offboarding.id} />}
                        </div>
                    </div>
                </div>

                <OffboardingSummaryCards progress={offboarding.progress} />

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <OffboardingTaskList
                        offboardingId={offboarding.id}
                        offboardingStatus={offboarding.status}
                        archived={offboarding.archived}
                        tasks={offboarding.tasks}
                        assigneeOptions={assigneeOptions}
                        canUpdateTasks={canUpdateTasks}
                        canSkipRequired={canSkipRequired}
                    />

                    <Card className="h-fit">
                        <CardHeader>
                            <CardTitle className="text-lg">Konteks offboarding</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm">
                            <Detail label="Tanggal keluar" value={offboarding.exit_date} />
                            <Detail label="Jenis keluar" value={offboardingExitTypeLabel(offboarding.exit_type)} />
                            <Detail label="Status akhir" value={offboarding.target_status?.name ?? 'Tidak tersedia'} />
                            <Detail label="Owner" value={offboarding.owner?.name ?? 'Tidak tersedia'} />
                            <Detail label="Contract" value={offboarding.contract?.contract_number ?? 'Tanpa contract'} />
                            <Detail label="Alasan" value={offboarding.exit_reason} />
                            {offboarding.notes && <Detail label="Catatan" value={offboarding.notes} />}
                            {offboarding.cancelled_by && (
                                <div className="border-destructive/30 bg-destructive/5 rounded-lg border p-3">
                                    <Detail label="Dibatalkan oleh" value={offboarding.cancelled_by.name} />
                                    {offboarding.cancelled_at && (
                                        <Detail label="Waktu pembatalan" value={new Date(offboarding.cancelled_at).toLocaleString('id-ID')} />
                                    )}
                                    {offboarding.cancel_reason && <Detail label="Alasan pembatalan" value={offboarding.cancel_reason} />}
                                </div>
                            )}
                            {offboarding.finalized_by && (
                                <div className="rounded-lg border border-emerald-500/30 bg-emerald-500/5 p-3">
                                    <Detail label="Difinalisasi oleh" value={offboarding.finalized_by.name} />
                                    {offboarding.finalized_at && (
                                        <Detail label="Waktu finalisasi" value={new Date(offboarding.finalized_at).toLocaleString('id-ID')} />
                                    )}
                                    {offboarding.finalization_business_date && (
                                        <Detail label="Business date finalisasi" value={offboarding.finalization_business_date} />
                                    )}
                                </div>
                            )}
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
            <div className="mt-1 font-medium whitespace-pre-wrap">{value}</div>
        </div>
    );
}
