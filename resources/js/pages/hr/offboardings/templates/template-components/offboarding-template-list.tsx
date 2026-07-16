import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@inertiajs/react';
import { ClipboardList } from 'lucide-react';
import type { TemplatePaginator } from '../types';

export function OffboardingTemplateList({ templates }: { templates: TemplatePaginator }) {
    if (templates.data.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center px-6 py-12 text-center">
                    <ClipboardList className="text-muted-foreground mb-3 size-9" aria-hidden="true" />
                    <h2 className="font-medium">Belum ada template offboarding</h2>
                    <p className="text-muted-foreground mt-1 max-w-sm text-sm">
                        Buat checklist pertama untuk handover, pengembalian aset, dan penutupan akses.
                    </p>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-4">
            {templates.data.map((template) => (
                <Card key={template.id}>
                    <CardHeader className="pb-3">
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <CardTitle className="truncate text-base">{template.name}</CardTitle>
                                <p className="text-muted-foreground mt-1 text-xs">{template.code}</p>
                            </div>
                            <Badge variant={template.active ? 'default' : 'secondary'}>{template.active ? 'Aktif' : 'Nonaktif'}</Badge>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {template.description && <p className="text-muted-foreground mb-3 text-sm">{template.description}</p>}
                        <ol className="space-y-2" aria-label={`Checklist ${template.name}`}>
                            {template.items.map((item, index) => (
                                <li key={item.id} className="bg-muted/50 flex gap-3 rounded-md border p-3 text-sm">
                                    <span className="font-medium">{index + 1}.</span>
                                    <div className="min-w-0 flex-1">
                                        <div className="font-medium">{item.title}</div>
                                        <div className="text-muted-foreground mt-1 text-xs">
                                            {item.category} · {formatDueOffset(item.due_offset_days)} · {item.required ? 'Wajib' : 'Opsional'}
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ol>
                    </CardContent>
                </Card>
            ))}

            {templates.last_page > 1 && (
                <nav className="flex items-center justify-between gap-3" aria-label="Pagination template offboarding">
                    <PageButton href={templates.prev_page_url}>Sebelumnya</PageButton>
                    <span className="text-muted-foreground text-xs">
                        Halaman {templates.current_page} dari {templates.last_page}
                    </span>
                    <PageButton href={templates.next_page_url}>Berikutnya</PageButton>
                </nav>
            )}
        </div>
    );
}

function PageButton({ href, children }: { href: string | null; children: string }) {
    return (
        <Button asChild={Boolean(href)} variant="outline" size="sm" disabled={!href}>
            {href ? (
                <Link href={href} preserveScroll>
                    {children}
                </Link>
            ) : (
                <span>{children}</span>
            )}
        </Button>
    );
}

function formatDueOffset(offset: number) {
    if (offset === 0) {
        return 'Hari H';
    }

    return offset < 0 ? `H${offset}` : `H+${offset}`;
}
