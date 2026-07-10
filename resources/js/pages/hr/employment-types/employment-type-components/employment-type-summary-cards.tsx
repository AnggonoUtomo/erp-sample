import { Card, CardContent } from '@/components/ui/card';
import { ArchiveRestore, BriefcaseBusiness, CalendarClock, Gift, ToggleRight } from 'lucide-react';
import type { EmploymentTypeSummary } from '../types';

type Props = {
    summary: EmploymentTypeSummary;
};

const cards = [
    { key: 'total', label: 'Total tipe', icon: BriefcaseBusiness, tone: 'icon-tone-sky' },
    { key: 'active', label: 'Aktif', icon: ToggleRight, tone: 'icon-tone-emerald' },
    { key: 'requires_contract_end_date', label: 'Perlu end date', icon: CalendarClock, tone: 'icon-tone-amber' },
    { key: 'eligible_for_benefits', label: 'Benefit', icon: Gift, tone: 'icon-tone-rose' },
    { key: 'archived', label: 'Diarsipkan', icon: ArchiveRestore, tone: 'icon-tone-amber' },
] as const;

export function EmploymentTypeSummaryCards({ summary }: Props) {
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
