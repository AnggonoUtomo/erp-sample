import { Button } from '@/components/ui/button';
import type { Paginator } from '../types';

type Props = {
    paginator: Paginator<unknown>;
    pageName: 'pending_page' | 'failed_page';
    onNavigate: (pageName: 'pending_page' | 'failed_page', page: number) => void;
};

export function QueuePager({ paginator, pageName, onNavigate }: Props) {
    const from = paginator.total ? (paginator.current_page - 1) * paginator.per_page + 1 : 0;
    const to = Math.min(paginator.current_page * paginator.per_page, paginator.total);

    return (
        <div className="flex items-center justify-between gap-4 pt-2">
            <p className="text-muted-foreground text-sm">{paginator.total ? `${from}-${to} of ${paginator.total}` : 'No results'}</p>
            <div className="flex items-center gap-2">
                <Button variant="outline" size="sm" disabled={paginator.current_page <= 1} onClick={() => onNavigate(pageName, paginator.current_page - 1)}>
                    Prev
                </Button>
                <span className="text-sm">
                    {paginator.current_page} / {paginator.last_page}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    disabled={paginator.current_page >= paginator.last_page}
                    onClick={() => onNavigate(pageName, paginator.current_page + 1)}
                >
                    Next
                </Button>
            </div>
        </div>
    );
}
