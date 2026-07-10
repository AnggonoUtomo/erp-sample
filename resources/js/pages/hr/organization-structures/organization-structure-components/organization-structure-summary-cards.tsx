import { Card, CardContent } from '@/components/ui/card';
import { ArchiveRestore, GitBranch, Network, ShieldCheck, UserRoundCog } from 'lucide-react';
import type { OrganizationStructureSummary } from '../types';

type Props = { summary: OrganizationStructureSummary };

const cards = [
    { key: 'total', label: 'Total struktur', icon: Network, tone: 'icon-tone-sky' },
    { key: 'active', label: 'Aktif', icon: ShieldCheck, tone: 'icon-tone-emerald' },
    { key: 'root_nodes', label: 'Root node', icon: GitBranch, tone: 'icon-tone-violet' },
    { key: 'position_nodes', label: 'Node posisi', icon: UserRoundCog, tone: 'icon-tone-cyan' },
    { key: 'archived', label: 'Diarsipkan', icon: ArchiveRestore, tone: 'icon-tone-amber' },
] as const;

export function OrganizationStructureSummaryCards({ summary }: Props) {
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
