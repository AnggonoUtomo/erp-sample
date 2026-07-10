import { Card, CardContent } from '@/components/ui/card';
import { ArchiveRestore, BadgeCheck, LogOut, ToggleLeft, ToggleRight } from 'lucide-react';
import type { EmploymentStatusSummary } from '../types';

type Props = {
    summary: EmploymentStatusSummary;
};

const cards = [
    { key: 'total', label: 'Total status', icon: BadgeCheck, tone: 'icon-tone-sky' },
    { key: 'active', label: 'Aktif', icon: ToggleRight, tone: 'icon-tone-emerald' },
    { key: 'inactive', label: 'Nonaktif', icon: ToggleLeft, tone: 'icon-tone-stone' },
    { key: 'is_final_status', label: 'Status akhir', icon: LogOut, tone: 'icon-tone-rose' },
    { key: 'archived', label: 'Diarsipkan', icon: ArchiveRestore, tone: 'icon-tone-amber' },
] as const;

export function EmploymentStatusSummaryCards({ summary }: Props) {
    return (
        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            {cards.map((card) => {
                const Icon = card.icon;

                return (
                    <Card key={card.key} data-dashboard-card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className={`dashboard-icon ${card.tone} flex size-10 items-center justify-center rounded-lg`}>
                                <Icon className="size-5" />
                            </span>
                            <div>
                                <p className="text-sm text-muted-foreground">{card.label}</p>
                                <p className="text-2xl font-semibold tracking-tight">{summary[card.key]}</p>
                            </div>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}
