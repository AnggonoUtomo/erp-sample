import { CircleHelp } from 'lucide-react';

export function HRReportEmptyState({ message }: { message: string }) {
    return (
        <div className="text-muted-foreground rounded-xl border border-dashed p-6 text-center text-sm" aria-live="polite">
            <CircleHelp className="mx-auto mb-2 size-5" aria-hidden="true" />
            <p>{message}</p>
        </div>
    );
}
