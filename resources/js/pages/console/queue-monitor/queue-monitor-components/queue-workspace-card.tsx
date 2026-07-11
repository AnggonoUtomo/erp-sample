import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router } from '@inertiajs/react';
import { AlertTriangle, Clock, ListRestart, RefreshCcw, RotateCcw, Trash2 } from 'lucide-react';
import type { FailedJob, Paginator, PendingJob, QueueOverview } from '../types';
import { QueuePager } from './queue-pager';

type Props = {
    overview: QueueOverview;
    pendingJobs: Paginator<PendingJob>;
    failedJobs: Paginator<FailedJob>;
    queues: string[];
    activeQueue: string;
    query: Record<string, string | number | undefined>;
    canManage: boolean;
    onQueueChange: (value: string) => void;
    onNavigate: (pageName: 'pending_page' | 'failed_page', page: number) => void;
};

function queueTone(queue: string) {
    return queue === 'default' ? 'bg-indigo-600 text-white' : 'bg-slate-600 text-white';
}

export function QueueWorkspaceCard({ overview, pendingJobs, failedJobs, queues, activeQueue, canManage, onQueueChange, onNavigate }: Props) {
    const retryJob = (job: FailedJob) => {
        if (!window.confirm(`Retry failed job ${job.uuid}?`)) {
            return;
        }

        router.post(route('queue-monitor.failed.retry', job.uuid), {}, { preserveScroll: true });
    };

    const deleteJob = (job: FailedJob) => {
        if (!window.confirm(`Hapus failed job ${job.uuid}?`)) {
            return;
        }

        router.delete(route('queue-monitor.failed.destroy', job.uuid), { preserveScroll: true });
    };

    const flushFailed = () => {
        if (!window.confirm('Hapus semua failed jobs? Aksi ini tidak bisa dibatalkan.')) {
            return;
        }

        router.delete(route('queue-monitor.failed.flush'), { preserveScroll: true });
    };

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="border-b">
                <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <CardTitle className="flex items-center gap-2">
                            <span className="dashboard-icon icon-tone-indigo flex size-10 items-center justify-center rounded-md">
                                <ListRestart className="size-5" />
                            </span>
                            Queue Workspace
                        </CardTitle>
                        <CardDescription>Filter queue dan kelola pekerjaan yang gagal.</CardDescription>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <Select value={activeQueue} onValueChange={onQueueChange}>
                            <SelectTrigger className="h-10 w-full sm:w-[180px]">
                                <SelectValue placeholder="Queue" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Queue</SelectItem>
                                {queues.map((queue) => (
                                    <SelectItem key={queue} value={queue}>
                                        {queue}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Button variant="outline" onClick={() => router.reload()}>
                            <RefreshCcw className="size-4" />
                            Refresh
                        </Button>
                        <Button variant="destructive" disabled={!canManage || !overview.failed} onClick={flushFailed}>
                            <Trash2 className="size-4" />
                            Clear Failed
                        </Button>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="space-y-6 p-5 sm:p-6">
                <section className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <h2 className="text-base font-semibold">Pending Jobs</h2>
                            <p className="text-muted-foreground text-sm">Pekerjaan yang masih menunggu diproses worker.</p>
                        </div>
                        <Badge variant="secondary">{pendingJobs.total} jobs</Badge>
                    </div>
                    <div className="overflow-hidden rounded-md border">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[760px] table-fixed text-sm">
                                <thead className="bg-muted/60 text-muted-foreground text-left">
                                    <tr>
                                        <th className="w-[12%] px-3 py-3 font-semibold">ID</th>
                                        <th className="w-[18%] px-3 py-3 font-semibold">Queue</th>
                                        <th className="w-[34%] px-3 py-3 font-semibold">Job</th>
                                        <th className="w-[12%] px-3 py-3 font-semibold">Attempts</th>
                                        <th className="w-[24%] px-3 py-3 font-semibold">Available</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {pendingJobs.data.length ? (
                                        pendingJobs.data.map((job) => (
                                            <tr key={job.id} className="border-t">
                                                <td className="px-3 py-3 font-medium">{job.id}</td>
                                                <td className="px-3 py-3">
                                                    <Badge className={queueTone(job.queue)}>{job.queue}</Badge>
                                                </td>
                                                <td className="px-3 py-3">
                                                    <div className="truncate font-medium">{job.name}</div>
                                                    <div className="text-muted-foreground truncate text-xs">Created {job.created_at ?? '-'}</div>
                                                </td>
                                                <td className="px-3 py-3">{job.attempts}</td>
                                                <td className="px-3 py-3">
                                                    <span className="flex items-center gap-2">
                                                        <Clock className="text-muted-foreground size-4" />
                                                        {job.available_at ?? '-'}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td className="text-muted-foreground px-3 py-8 text-center" colSpan={5}>
                                                Tidak ada pending job.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <QueuePager paginator={pendingJobs} pageName="pending_page" onNavigate={onNavigate} />
                </section>

                <section className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <h2 className="text-base font-semibold">Failed Jobs</h2>
                            <p className="text-muted-foreground text-sm">Pekerjaan yang gagal dan bisa di-retry atau dihapus.</p>
                        </div>
                        <Badge variant={failedJobs.total ? 'destructive' : 'secondary'}>{failedJobs.total} failed</Badge>
                    </div>
                    <div className="overflow-hidden rounded-md border">
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[920px] table-fixed text-sm">
                                <thead className="bg-muted/60 text-muted-foreground text-left">
                                    <tr>
                                        <th className="w-[16%] px-3 py-3 font-semibold">Queue</th>
                                        <th className="w-[24%] px-3 py-3 font-semibold">Job</th>
                                        <th className="w-[34%] px-3 py-3 font-semibold">Exception</th>
                                        <th className="w-[16%] px-3 py-3 font-semibold">Failed At</th>
                                        <th className="w-[10%] px-3 py-3 text-right font-semibold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {failedJobs.data.length ? (
                                        failedJobs.data.map((job) => (
                                            <tr key={job.uuid} className="border-t align-top">
                                                <td className="px-3 py-3">
                                                    <Badge className={queueTone(job.queue)}>{job.queue}</Badge>
                                                    <p className="text-muted-foreground mt-1 truncate text-xs">{job.connection}</p>
                                                </td>
                                                <td className="px-3 py-3">
                                                    <p className="truncate font-medium">{job.name}</p>
                                                    <p className="text-muted-foreground truncate text-xs">{job.uuid}</p>
                                                </td>
                                                <td className="px-3 py-3">
                                                    <p className="text-muted-foreground line-clamp-3 text-xs leading-relaxed">{job.exception}</p>
                                                </td>
                                                <td className="px-3 py-3">{job.failed_at}</td>
                                                <td className="px-3 py-3">
                                                    <div className="flex justify-end gap-2">
                                                        <Button
                                                            size="icon"
                                                            variant="outline"
                                                            disabled={!canManage}
                                                            onClick={() => retryJob(job)}
                                                            title="Retry job"
                                                        >
                                                            <RotateCcw className="size-4" />
                                                        </Button>
                                                        <Button
                                                            size="icon"
                                                            variant="destructive"
                                                            disabled={!canManage}
                                                            onClick={() => deleteJob(job)}
                                                            title="Delete failed job"
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td className="text-muted-foreground px-3 py-8 text-center" colSpan={5}>
                                                <AlertTriangle className="mx-auto mb-2 size-5" />
                                                Tidak ada failed job.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <QueuePager paginator={failedJobs} pageName="failed_page" onNavigate={onNavigate} />
                </section>
            </CardContent>
        </Card>
    );
}
