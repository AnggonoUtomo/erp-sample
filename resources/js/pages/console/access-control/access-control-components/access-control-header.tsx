import { ShieldCheck } from 'lucide-react';

export function AccessControlHeader() {
    return (
        <div className="bg-card flex items-center gap-4 rounded-lg border p-5 shadow-xs">
            <div className="flex size-11 shrink-0 items-center justify-center rounded-lg bg-violet-500/10 text-violet-600">
                <ShieldCheck className="size-5" />
            </div>
            <div className="min-w-0">
                <h1 className="text-xl font-bold tracking-tight">Access Control</h1>
                <p className="text-muted-foreground mt-1 text-sm">Pilih role, lalu atur permission berdasarkan module.</p>
            </div>
        </div>
    );
}
