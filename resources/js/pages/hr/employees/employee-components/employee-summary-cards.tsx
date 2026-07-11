import { Card, CardContent } from '@/components/ui/card';
import { Archive, Link2, UserCheck, UsersRound } from 'lucide-react';
import type { EmployeeSummary } from '../types';

type Props = {
    summary: EmployeeSummary;
};

export function EmployeeSummaryCards({ summary }: Props) {
    const cards = [
        { label: 'Total Employee', value: summary.total, icon: UsersRound, tone: 'icon-tone-sky' },
        { label: 'Aktif', value: summary.active, icon: UserCheck, tone: 'icon-tone-emerald' },
        { label: 'Linked User', value: summary.linked_users, icon: Link2, tone: 'icon-tone-violet' },
        { label: 'Arsip', value: summary.archived, icon: Archive, tone: 'icon-tone-amber' },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {cards.map((card) => (
                <Card key={card.label} data-dashboard-card>
                    <CardContent className="flex items-center justify-between gap-4 p-5">
                        <div>
                            <p className="text-muted-foreground text-sm">{card.label}</p>
                            <p className="mt-1 text-2xl font-semibold">{card.value}</p>
                        </div>
                        <span className={`dashboard-icon ${card.tone} flex size-11 items-center justify-center rounded-lg`}>
                            <card.icon className="size-5" />
                        </span>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
}
