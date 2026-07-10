export type Heartbeat = {
    last_run_at: string | null;
    age_seconds: number | null;
    status: 'fresh' | 'stale' | 'down' | 'never';
};

export type SchedulerOverview = {
    timezone: string;
    php_binary: string;
    artisan_path: string;
    cron_command: string;
    tasks: number;
    due_24h: number;
    heartbeat: Heartbeat;
};

export type ScheduledEvent = {
    expression: string;
    command: string;
    description: string;
    timezone: string;
    next_due: string | null;
    next_due_human: string;
    is_due_soon: boolean;
};

export const heartbeatLabels: Record<Heartbeat['status'], string> = {
    fresh: 'Fresh',
    stale: 'Stale',
    down: 'Down',
    never: 'Never Run',
};

export const heartbeatClasses: Record<Heartbeat['status'], string> = {
    fresh: 'bg-emerald-600 text-white',
    stale: 'bg-amber-500 text-white',
    down: 'bg-red-600 text-white',
    never: 'bg-slate-600 text-white',
};

export const heartbeatDescriptions: Record<Heartbeat['status'], string> = {
    fresh: 'Scheduler aktif. Heartbeat tercatat dalam 2 menit terakhir.',
    stale: 'Scheduler pernah jalan, tapi heartbeat mulai terlambat. Cek cron jika status ini sering muncul.',
    down: 'Scheduler kemungkinan tidak berjalan. Cron server perlu dicek.',
    never: 'Heartbeat belum pernah tercatat. Jalankan scheduler atau cek konfigurasi cron.',
};

export function secondsLabel(value: number | null) {
    if (value === null) {
        return '-';
    }

    if (value < 60) {
        return `${value}s`;
    }

    const minutes = Math.floor(value / 60);
    const seconds = value % 60;

    return `${minutes}m ${seconds}s`;
}
