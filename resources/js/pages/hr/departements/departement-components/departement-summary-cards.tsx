import { Card, CardContent } from '@/components/ui/card';
import { Building2, UsersRound, X } from 'lucide-react';
import type { ComponentType } from 'react';
import type { DepartementSummary } from '../types';

type SummaryItem = {
    label: string;
    value: number;
    icon: ComponentType<{ className?: string }>;
    tone: string;
};

export function DepartementSummaryCards({ summary }: { summary: DepartementSummary }) {
    const items: SummaryItem[] = [
        {
            label: 'Total Departement',
            value: summary.total,
            icon: Building2,
            tone: 'bg-primary/10 text-primary',
        },
        {
            label: 'Aktif',
            value: summary.active,
            icon: UsersRound,
            tone: 'bg-emerald-500/10 text-emerald-600',
        },
        {
            label: 'Nonaktif',
            value: summary.inactive,
            icon: X,
            tone: 'bg-amber-500/10 text-amber-600',
        },
        {
            label: 'Root Unit',
            value: summary.root,
            icon: Building2,
            tone: 'bg-sky-500/10 text-sky-600',
        },
    ];

    return (
        <section className="grid gap-4 md:grid-cols-4">
            {items.map((item) => {
                const Icon = item.icon;

                return (
                    <Card key={item.label}>
                        <CardContent className="flex items-center gap-3 p-4">
                            <div className={`flex size-10 items-center justify-center rounded-lg ${item.tone}`}>
                                <Icon className="size-5" />
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">{item.label}</p>
                                <p className="text-2xl font-semibold">{item.value}</p>
                            </div>
                        </CardContent>
                    </Card>
                );
            })}
        </section>
    );
}
