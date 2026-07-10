import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface PaginationBarProps {
    meta: PaginationMeta;
    routeName: string;
    filters?: Record<string, string | number | null | undefined>;
    idPrefix?: string;
}

export function PaginationBar({ meta, routeName, filters = {}, idPrefix = 'pagination' }: PaginationBarProps) {
    const { pagination } = usePage<SharedData>().props;
    const options = pagination?.per_page_options?.length ? pagination.per_page_options : [5, 10, 15, 25, 50];

    const navigate = (page: number) => {
        router.get(route(routeName), { ...filters, page }, { preserveScroll: true, preserveState: true });
    };

    const changePerPage = (value: string) => {
        router.get(route(routeName), { ...filters, per_page: value, page: 1 }, { preserveScroll: true, preserveState: true });
    };

    const from = (meta.current_page - 1) * meta.per_page + 1;
    const to = Math.min(meta.current_page * meta.per_page, meta.total);

    return (
        <div className="flex items-center justify-between gap-4 pt-2">
            <p className="text-muted-foreground text-sm">{meta.total > 0 ? `${from}-${to} of ${meta.total}` : 'No results'}</p>
            <div className="flex items-center gap-2">
                <Select value={String(meta.per_page)} onValueChange={changePerPage}>
                    <SelectTrigger id={`${idPrefix}-rows-filter-trigger`} className="h-8 w-[70px]">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {options.map((n) => (
                            <SelectItem key={n} value={String(n)}>
                                {n}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Button
                    id={`${idPrefix}-pagination-previous`}
                    variant="outline"
                    size="icon"
                    className="h-8 w-8"
                    disabled={meta.current_page <= 1}
                    onClick={() => navigate(meta.current_page - 1)}
                >
                    <ChevronLeft className="h-4 w-4" />
                </Button>
                <span className="text-sm">
                    {meta.current_page} / {meta.last_page}
                </span>
                <Button
                    id={`${idPrefix}-pagination-next`}
                    variant="outline"
                    size="icon"
                    className="h-8 w-8"
                    disabled={meta.current_page >= meta.last_page}
                    onClick={() => navigate(meta.current_page + 1)}
                >
                    <ChevronRight className="h-4 w-4" />
                </Button>
            </div>
        </div>
    );
}
