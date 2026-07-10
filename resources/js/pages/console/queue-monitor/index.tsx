import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useMemo } from 'react';
import { QueueMonitorHeader } from './queue-monitor-components/queue-monitor-header';
import { QueueOverviewCards } from './queue-monitor-components/queue-overview-cards';
import { QueueWorkspaceCard } from './queue-monitor-components/queue-workspace-card';
import type { FailedJob, Paginator, PendingJob, QueueOverview } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Queue Monitor',
        href: '/queue-monitor',
    },
];

type Props = {
    overview: QueueOverview;
    pendingJobs: Paginator<PendingJob>;
    failedJobs: Paginator<FailedJob>;
    queues: string[];
    filters: {
        queue?: string | null;
        per_page?: number | null;
    };
    can: {
        manage: boolean;
    };
};

export default function QueueMonitor({ overview, pendingJobs, failedJobs, queues, filters, can }: Props) {
    const activeQueue = filters.queue ?? 'all';

    const query = useMemo(
        () => ({
            queue: activeQueue === 'all' ? undefined : activeQueue,
            per_page: filters.per_page ?? pendingJobs.per_page,
        }),
        [activeQueue, filters.per_page, pendingJobs.per_page],
    );

    const changeQueue = (value: string) => {
        router.get(
            route('queue-monitor.index'),
            {
                ...query,
                queue: value === 'all' ? undefined : value,
                pending_page: 1,
                failed_page: 1,
            },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const goToPage = (pageName: 'pending_page' | 'failed_page', page: number) => {
        router.get(route('queue-monitor.index'), { ...query, [pageName]: page }, { preserveScroll: true, preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Queue Monitor" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <QueueMonitorHeader connection={overview.connection} />
                <QueueOverviewCards overview={overview} />
                <QueueWorkspaceCard
                    overview={overview}
                    pendingJobs={pendingJobs}
                    failedJobs={failedJobs}
                    queues={queues}
                    activeQueue={activeQueue}
                    query={query}
                    canManage={can.manage}
                    onQueueChange={changeQueue}
                    onNavigate={goToPage}
                />
            </div>
        </AppLayout>
    );
}
