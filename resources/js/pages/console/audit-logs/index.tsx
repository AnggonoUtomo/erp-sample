import { PaginationBar } from '@/components/pagination-bar';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Activity, Clock, Database, Filter, Search, ShieldCheck, UserRound } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { AuditLogHeader } from './audit-log-components/audit-log-header';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Audit Logs',
        href: '/audit-logs',
    },
];

type AuditActor = {
    id: number;
    name: string;
    email: string;
};

type AuditLog = {
    id: number;
    module: string;
    event: string;
    description: string | null;
    actor: AuditActor | null;
    auditable_type: string | null;
    auditable_id: number | string | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string | null;
};

type PaginatedAuditLogs = {
    data: AuditLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type Props = {
    logs: PaginatedAuditLogs;
    modules: string[];
    events: string[];
    filters: {
        search?: string | null;
        module?: string | null;
        event?: string | null;
        per_page?: number;
    };
};

const moduleToneMap: Record<string, string> = {
    'user-management': 'bg-indigo-600 text-white',
    'access-control': 'bg-violet-600 text-white',
    'system-settings': 'bg-purple-600 text-white',
};

function initials(name?: string | null) {
    if (!name) {
        return 'SY';
    }

    return name
        .split(' ')
        .filter(Boolean)
        .map((part) => part.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function prettyJson(values: Record<string, unknown> | null) {
    if (!values || !Object.keys(values).length) {
        return '-';
    }

    return JSON.stringify(values, null, 2);
}

function eventLabel(event: string) {
    return event.replaceAll('.', ' ').replaceAll('_', ' ');
}

export default function AuditLogs({ logs, modules, events, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [module, setModule] = useState(filters.module ?? 'all');
    const [event, setEvent] = useState(filters.event ?? 'all');
    const [selectedLog, setSelectedLog] = useState<AuditLog | null>(logs.data[0] ?? null);
    const filterMounted = useRef(false);

    const activeFilters = useMemo(
        () => ({
            search,
            module: module === 'all' ? undefined : module,
            event: event === 'all' ? undefined : event,
            per_page: filters.per_page ?? logs.per_page,
        }),
        [event, filters.per_page, logs.per_page, module, search],
    );

    useEffect(() => {
        if (!filterMounted.current) {
            filterMounted.current = true;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(route('audit-logs.index'), activeFilters, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 400);

        return () => clearTimeout(timeout);
    }, [activeFilters]);

    useEffect(() => {
        setSelectedLog(logs.data[0] ?? null);
    }, [logs.data]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Audit Logs" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <AuditLogHeader total={logs.total} />

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <Card data-dashboard-card className="min-w-0 overflow-hidden">
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2">
                                <span className="dashboard-icon icon-tone-orange flex size-10 items-center justify-center rounded-md">
                                    <Activity className="size-5" />
                                </span>
                                Riwayat Aktivitas
                            </CardTitle>
                            <CardDescription>Gunakan filter untuk menemukan aktivitas yang relevan.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-5 sm:p-6">
                            <div className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_200px]">
                                <div className="relative">
                                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                    <Input
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Cari deskripsi, event, module, atau aktor..."
                                        className="h-11 pl-9"
                                    />
                                </div>

                                <Select value={module} onValueChange={setModule}>
                                    <SelectTrigger className="h-11">
                                        <SelectValue placeholder="Module" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Module</SelectItem>
                                        {modules.map((item) => (
                                            <SelectItem key={item} value={item}>
                                                {item}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Select value={event} onValueChange={setEvent}>
                                    <SelectTrigger className="h-11">
                                        <SelectValue placeholder="Event" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Event</SelectItem>
                                        {events.map((item) => (
                                            <SelectItem key={item} value={item}>
                                                {item}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="overflow-hidden rounded-md border">
                                <div className="overflow-x-auto">
                                    <table className="w-full min-w-[760px] table-fixed text-sm">
                                        <thead className="bg-muted/60 text-muted-foreground text-left">
                                            <tr>
                                                <th className="w-[20%] px-3 py-3 font-semibold">Waktu</th>
                                                <th className="w-[20%] px-3 py-3 font-semibold">Actor</th>
                                                <th className="w-[18%] px-3 py-3 font-semibold">Module</th>
                                                <th className="w-[18%] px-3 py-3 font-semibold">Event</th>
                                                <th className="w-[24%] px-3 py-3 font-semibold">Deskripsi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {logs.data.length ? (
                                                logs.data.map((log) => (
                                                    <tr
                                                        key={log.id}
                                                        className="hover:bg-muted/40 cursor-pointer border-t transition"
                                                        onClick={() => setSelectedLog(log)}
                                                    >
                                                        <td className="px-3 py-3">
                                                            <span className="flex items-center gap-2">
                                                                <Clock className="text-muted-foreground size-4" />
                                                                {log.created_at ?? '-'}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <div className="flex min-w-0 items-center gap-2">
                                                                <Avatar className="size-8 rounded-lg">
                                                                    <AvatarFallback className="rounded-lg text-xs">
                                                                        {initials(log.actor?.name)}
                                                                    </AvatarFallback>
                                                                </Avatar>
                                                                <span className="min-w-0">
                                                                    <span className="block truncate font-medium">{log.actor?.name ?? 'System'}</span>
                                                                    <span className="text-muted-foreground block truncate text-xs">
                                                                        {log.actor?.email ?? 'system'}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <Badge className={moduleToneMap[log.module] ?? 'bg-slate-600 text-white'}>
                                                                {log.module}
                                                            </Badge>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <span className="capitalize">{eventLabel(log.event)}</span>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <span className="line-clamp-2">{log.description ?? '-'}</span>
                                                        </td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={5} className="text-muted-foreground h-24 px-3 py-6 text-center">
                                                        Belum ada audit log.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div className="text-muted-foreground text-sm">
                                    Showing {logs.data.length} of {logs.total} logs
                                </div>
                                <PaginationBar meta={logs} routeName="audit-logs.index" filters={activeFilters} idPrefix="audit-log" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card data-dashboard-card className="h-fit overflow-hidden">
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <span className="dashboard-icon icon-tone-indigo flex size-8 items-center justify-center rounded-md">
                                    <Filter className="size-4" />
                                </span>
                                Detail Log
                            </CardTitle>
                            <CardDescription>Perubahan dan konteks request.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-5 p-5">
                            {selectedLog ? (
                                <>
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between gap-3">
                                            <span className="text-muted-foreground text-sm">Event</span>
                                            <Badge variant="outline" className="capitalize">
                                                {eventLabel(selectedLog.event)}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center justify-between gap-3">
                                            <span className="text-muted-foreground text-sm">Module</span>
                                            <Badge className={moduleToneMap[selectedLog.module] ?? 'bg-slate-600 text-white'}>
                                                {selectedLog.module}
                                            </Badge>
                                        </div>
                                        <div className="flex items-center justify-between gap-3">
                                            <span className="text-muted-foreground text-sm">Target</span>
                                            <span className="text-right text-sm font-medium">
                                                {selectedLog.auditable_type ? `${selectedLog.auditable_type} #${selectedLog.auditable_id}` : '-'}
                                            </span>
                                        </div>
                                    </div>

                                    <div className="bg-background/60 rounded-lg border p-4">
                                        <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                            <UserRound className="size-4 text-indigo-500" />
                                            Actor
                                        </div>
                                        <p className="text-sm">{selectedLog.actor?.name ?? 'System'}</p>
                                        <p className="text-muted-foreground text-xs">{selectedLog.actor?.email ?? 'system'}</p>
                                    </div>

                                    <div className="bg-background/60 rounded-lg border p-4">
                                        <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                            <Database className="size-4 text-violet-500" />
                                            Request
                                        </div>
                                        <div className="grid gap-2 text-sm">
                                            <div className="flex justify-between gap-3">
                                                <span className="text-muted-foreground">IP</span>
                                                <span className="text-right">{selectedLog.ip_address ?? '-'}</span>
                                            </div>
                                            <div className="space-y-1">
                                                <span className="text-muted-foreground">User Agent</span>
                                                <p className="text-xs break-words">{selectedLog.user_agent ?? '-'}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-3">
                                        <div>
                                            <p className="mb-2 text-sm font-medium">Old Values</p>
                                            <pre className="bg-muted/60 max-h-52 overflow-auto rounded-lg border p-3 text-xs">
                                                {prettyJson(selectedLog.old_values)}
                                            </pre>
                                        </div>
                                        <div>
                                            <p className="mb-2 text-sm font-medium">New Values</p>
                                            <pre className="bg-muted/60 max-h-52 overflow-auto rounded-lg border p-3 text-xs">
                                                {prettyJson(selectedLog.new_values)}
                                            </pre>
                                        </div>
                                    </div>
                                </>
                            ) : (
                                <div className="flex min-h-[360px] flex-col items-center justify-center gap-3 text-center">
                                    <div className="dashboard-icon icon-tone-orange flex size-14 items-center justify-center rounded-lg">
                                        <ShieldCheck className="size-7" />
                                    </div>
                                    <div>
                                        <p className="font-medium">Belum ada log dipilih</p>
                                        <p className="text-muted-foreground mt-1 text-sm">Pilih salah satu aktivitas dari tabel.</p>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
