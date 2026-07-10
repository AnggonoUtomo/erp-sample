import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { BellRing, MailCheck } from 'lucide-react';
import type { NotificationTemplate } from '../types';

type Props = {
    templates: NotificationTemplate[];
    selectedTemplate: NotificationTemplate | null;
    onSelect: (template: NotificationTemplate) => void;
};

export function TemplateListCard({ templates, selectedTemplate, onSelect }: Props) {
    return (
        <Card data-dashboard-card className="h-fit overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-emerald flex size-10 items-center justify-center rounded-md">
                        <BellRing className="size-5" />
                    </span>
                    Template
                </CardTitle>
                <CardDescription>Pilih template yang ingin diedit.</CardDescription>
            </CardHeader>
            <CardContent className="space-y-2 p-3">
                {templates.map((template) => (
                    <button
                        key={template.id}
                        type="button"
                        onClick={() => onSelect(template)}
                        className={cn(
                            'hover:bg-muted/70 flex w-full items-start gap-3 rounded-md border p-3 text-left transition',
                            selectedTemplate?.id === template.id && 'border-primary bg-primary/5',
                        )}
                    >
                        <span className="dashboard-icon icon-tone-sky mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-md">
                            <MailCheck className="size-4" />
                        </span>
                        <span className="min-w-0 flex-1">
                            <span className="block truncate text-sm font-medium">{template.name}</span>
                            <span className="text-muted-foreground mt-1 block truncate text-xs">{template.key}</span>
                            <span className="mt-2 flex items-center gap-2">
                                <Badge variant={template.active ? 'default' : 'secondary'}>{template.active ? 'Aktif' : 'Nonaktif'}</Badge>
                                <Badge variant="outline" className="uppercase">
                                    {template.channel}
                                </Badge>
                            </span>
                        </span>
                    </button>
                ))}
            </CardContent>
        </Card>
    );
}
