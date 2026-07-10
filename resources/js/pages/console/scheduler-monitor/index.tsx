import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { ScheduledTaskCard } from './scheduler-monitor-components/scheduled-task-card';
import { SchedulerMonitorHeader } from './scheduler-monitor-components/scheduler-monitor-header';
import { SchedulerOverviewCards } from './scheduler-monitor-components/scheduler-overview-cards';
import { SchedulerSidePanels } from './scheduler-monitor-components/scheduler-side-panels';
import type { ScheduledEvent, SchedulerOverview } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Scheduler Monitor',
        href: '/scheduler-monitor',
    },
];

type Props = {
    overview: SchedulerOverview;
    events: ScheduledEvent[];
    can: {
        manage: boolean;
    };
};

export default function SchedulerMonitor({ overview, events, can }: Props) {
    const runScheduler = () => {
        if (!window.confirm('Jalankan schedule:run sekarang? Task yang due akan dieksekusi.')) {
            return;
        }

        router.post(route('scheduler-monitor.run'), {}, { preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Scheduler Monitor" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <SchedulerMonitorHeader overview={overview} />
                <SchedulerOverviewCards overview={overview} />

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <ScheduledTaskCard events={events} canManage={can.manage} onRun={runScheduler} />
                    <SchedulerSidePanels overview={overview} />
                </div>
            </div>
        </AppLayout>
    );
}
