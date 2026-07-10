import { PaginationBar } from '@/components/pagination-bar';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { CheckCircle2, Clock, Laptop, LogIn, Search, ShieldAlert, UserRound, XCircle } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { LoginActivityHeader } from './login-activity-components/login-activity-header';
import { LoginSummaryCards } from './login-activity-components/login-summary-cards';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Login Activity',
        href: '/login-activities',
    },
];

type LoginActivity = {
    id: number;
    user: { id: number; name: string; email: string } | null;
    email: string | null;
    event: string;
    successful: boolean;
    ip_address: string | null;
    device: string | null;
    browser: string | null;
    platform: string | null;
    message: string | null;
    user_agent: string | null;
    occurred_at: string | null;
};

type PaginatedActivities = {
    data: LoginActivity[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type Props = {
    activities: PaginatedActivities;
    summary: {
        total: number;
        success: number;
        failed: number;
        today: number;
    };
    events: string[];
    filters: {
        search?: string | null;
        event?: string | null;
        status?: string | null;
        per_page?: number;
    };
};

function initials(name?: string | null) {
    return (name ?? 'Unknown')
        .split(' ')
        .filter(Boolean)
        .map((part) => part.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function eventLabel(event: string) {
    return event.replaceAll('_', ' ').replaceAll('.', ' ');
}

export default function LoginActivities({ activities, summary, events, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [event, setEvent] = useState(filters.event ?? 'all');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [selected, setSelected] = useState<LoginActivity | null>(activities.data[0] ?? null);
    const filterMounted = useRef(false);

    const activeFilters = useMemo(
        () => ({
            search,
            event: event === 'all' ? undefined : event,
            status: status === 'all' ? undefined : status,
            per_page: filters.per_page ?? activities.per_page,
        }),
        [activities.per_page, event, filters.per_page, search, status],
    );

    useEffect(() => {
        if (!filterMounted.current) {
            filterMounted.current = true;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(route('login-activities.index'), activeFilters, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        }, 400);

        return () => clearTimeout(timeout);
    }, [activeFilters]);

    useEffect(() => {
        setSelected(activities.data[0] ?? null);
    }, [activities.data]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Login Activity" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <LoginActivityHeader total={activities.total} />
                <LoginSummaryCards summary={summary} />

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <Card data-dashboard-card className="min-w-0 overflow-hidden">
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2">
                                <span className="dashboard-icon icon-tone-sky flex size-10 items-center justify-center rounded-md">
                                    <LogIn className="size-5" />
                                </span>
                                Aktivitas Login
                            </CardTitle>
                            <CardDescription>Gunakan filter untuk menelusuri aktivitas akses user.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4 p-5 sm:p-6">
                            <div className="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px]">
                                <div className="relative">
                                    <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                                    <Input
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Cari user, email, IP, atau pesan..."
                                        className="h-11 pl-9"
                                    />
                                </div>
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger className="h-11">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">Semua Status</SelectItem>
                                        <SelectItem value="success">Success</SelectItem>
                                        <SelectItem value="failed">Failed</SelectItem>
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
                                                {eventLabel(item)}
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
                                                <th className="w-[28%] px-3 py-3 font-semibold">User</th>
                                                <th className="w-[15%] px-3 py-3 font-semibold">Event</th>
                                                <th className="w-[17%] px-3 py-3 font-semibold">Device</th>
                                                <th className="w-[20%] px-3 py-3 font-semibold">IP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {activities.data.length ? (
                                                activities.data.map((activity) => (
                                                    <tr
                                                        key={activity.id}
                                                        className="hover:bg-muted/40 cursor-pointer border-t transition"
                                                        onClick={() => setSelected(activity)}
                                                    >
                                                        <td className="px-3 py-3">
                                                            <span className="flex items-center gap-2">
                                                                <Clock className="text-muted-foreground size-4" />
                                                                {activity.occurred_at ?? '-'}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <div className="flex min-w-0 items-center gap-2">
                                                                <Avatar className="size-8 rounded-lg">
                                                                    <AvatarFallback className="rounded-lg text-xs">
                                                                        {initials(activity.user?.name ?? activity.email)}
                                                                    </AvatarFallback>
                                                                </Avatar>
                                                                <span className="min-w-0">
                                                                    <span className="block truncate font-medium">
                                                                        {activity.user?.name ?? 'Unknown'}
                                                                    </span>
                                                                    <span className="text-muted-foreground block truncate text-xs">
                                                                        {activity.email ?? '-'}
                                                                    </span>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <Badge variant={activity.successful ? 'default' : 'destructive'} className="capitalize">
                                                                {eventLabel(activity.event)}
                                                            </Badge>
                                                        </td>
                                                        <td className="px-3 py-3">
                                                            <span className="flex items-center gap-2">
                                                                <Laptop className="text-muted-foreground size-4" />
                                                                {activity.device ?? '-'}
                                                            </span>
                                                        </td>
                                                        <td className="px-3 py-3">{activity.ip_address ?? '-'}</td>
                                                    </tr>
                                                ))
                                            ) : (
                                                <tr>
                                                    <td colSpan={5} className="text-muted-foreground h-24 px-3 py-6 text-center">
                                                        Belum ada login activity.
                                                    </td>
                                                </tr>
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div className="text-muted-foreground text-sm">
                                    Showing {activities.data.length} of {activities.total} activities
                                </div>
                                <PaginationBar
                                    meta={activities}
                                    routeName="login-activities.index"
                                    filters={activeFilters}
                                    idPrefix="login-activity"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <Card data-dashboard-card className="h-fit overflow-hidden">
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <span className="dashboard-icon icon-tone-indigo flex size-8 items-center justify-center rounded-md">
                                    <UserRound className="size-4" />
                                </span>
                                Detail Activity
                            </CardTitle>
                            <CardDescription>Konteks browser, IP, dan status login.</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-5 p-5">
                            {selected ? (
                                <>
                                    <div className="flex items-center gap-3">
                                        {selected.successful ? (
                                            <CheckCircle2 className="size-5 text-emerald-500" />
                                        ) : (
                                            <XCircle className="text-destructive size-5" />
                                        )}
                                        <div>
                                            <p className="font-medium capitalize">{eventLabel(selected.event)}</p>
                                            <p className="text-muted-foreground text-sm">{selected.message ?? '-'}</p>
                                        </div>
                                    </div>

                                    <div className="grid gap-3 text-sm">
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">Email</span>
                                            <span className="text-right font-medium">{selected.email ?? '-'}</span>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">IP Address</span>
                                            <span className="text-right font-medium">{selected.ip_address ?? '-'}</span>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">Browser</span>
                                            <span className="text-right font-medium">{selected.browser ?? '-'}</span>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">Platform</span>
                                            <span className="text-right font-medium">{selected.platform ?? '-'}</span>
                                        </div>
                                        <div className="flex justify-between gap-3">
                                            <span className="text-muted-foreground">Device</span>
                                            <span className="text-right font-medium">{selected.device ?? '-'}</span>
                                        </div>
                                    </div>

                                    <div className="rounded-lg border p-4">
                                        <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                            <ShieldAlert className="size-4 text-amber-500" />
                                            User Agent
                                        </div>
                                        <p className="text-muted-foreground text-xs break-words">{selected.user_agent ?? '-'}</p>
                                    </div>
                                </>
                            ) : (
                                <div className="text-muted-foreground flex min-h-[320px] items-center justify-center text-center text-sm">
                                    Pilih aktivitas dari tabel.
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
