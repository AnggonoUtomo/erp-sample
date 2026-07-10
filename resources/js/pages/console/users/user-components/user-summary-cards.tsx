import { Card, CardContent } from '@/components/ui/card';
import type { UserRow } from '@/pages/console/users/types';
import { ShieldCheck, UserCheck, UserRound, UserX } from 'lucide-react';

type Props = {
    users: UserRow[];
    total: number;
    roleCount: number;
};

export default function UserSummaryCards({ users, total, roleCount }: Props) {
    const activeCount = users.filter((user) => user.status === 'active').length;
    const suspendedCount = users.filter((user) => user.status === 'suspended').length;
    const inactiveCount = users.filter((user) => user.status === 'inactive').length;

    const items = [
        {
            label: 'Total Users',
            value: total,
            note: 'Seluruh akun terdaftar',
            icon: UserRound,
            tone: 'bg-sky-500/10 text-sky-600',
        },
        {
            label: 'Active',
            value: activeCount,
            note: 'Pada halaman ini',
            icon: UserCheck,
            tone: 'bg-emerald-500/10 text-emerald-600',
        },
        {
            label: 'Inactive / Suspended',
            value: inactiveCount + suspendedCount,
            note: 'Perlu evaluasi akses',
            icon: UserX,
            tone: 'bg-rose-500/10 text-rose-600',
        },
        {
            label: 'Roles',
            value: roleCount,
            note: 'Role tersedia',
            icon: ShieldCheck,
            tone: 'bg-violet-500/10 text-violet-600',
        },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {items.map((item) => (
                <Card key={item.label} className="overflow-hidden">
                    <CardContent className="flex items-center gap-4 p-5">
                        <div className={`flex size-11 shrink-0 items-center justify-center rounded-xl ${item.tone}`}>
                            <item.icon className="size-5" />
                        </div>
                        <div className="min-w-0">
                            <p className="text-muted-foreground truncate text-sm">{item.label}</p>
                            <div className="mt-1 flex items-end gap-2">
                                <p className="text-2xl font-semibold">{item.value}</p>
                                <p className="text-muted-foreground pb-1 text-xs">{item.note}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
