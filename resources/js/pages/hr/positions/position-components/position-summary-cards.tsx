import { Card, CardContent } from '@/components/ui/card';
import { BriefcaseBusiness, Building2, CheckCircle2, X } from 'lucide-react';
import type { ComponentType } from 'react';
import type { PositionSummary } from '../types';

type SummaryItem = {
    title: string;
    value: number;
    description: string;
    icon: ComponentType<{ className?: string }>;
    tone: string;
};

export function PositionSummaryCards({ summary }: { summary: PositionSummary }) {
    const items: SummaryItem[] = [
        {
            title: 'Total Positions',
            value: summary.total,
            description: 'Seluruh jabatan yang terdaftar.',
            icon: BriefcaseBusiness,
            tone: 'icon-tone-sky',
        },
        {
            title: 'Aktif',
            value: summary.active,
            description: 'Bisa dipakai pada employee profile.',
            icon: CheckCircle2,
            tone: 'icon-tone-emerald',
        },
        {
            title: 'Nonaktif',
            value: summary.inactive,
            description: 'Disimpan untuk histori organisasi.',
            icon: X,
            tone: 'icon-tone-rose',
        },
        {
            title: 'Departement',
            value: summary.departements,
            description: 'Unit organisasi yang punya position.',
            icon: Building2,
            tone: 'icon-tone-violet',
        },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {items.map((item) => {
                const Icon = item.icon;

                return (
                    <Card key={item.title} data-dashboard-card>
                        <CardContent className="flex gap-4 p-4">
                            <span className={`dashboard-icon ${item.tone} flex size-10 items-center justify-center rounded-lg`}>
                                <Icon className="size-5" />
                            </span>
                            <div>
                                <p className="text-sm font-medium">{item.title}</p>
                                <p className="mt-2 text-2xl font-semibold">{item.value}</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-5">{item.description}</p>
                            </div>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}
