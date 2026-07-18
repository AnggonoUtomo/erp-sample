import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { FileText, ShieldCheck } from 'lucide-react';
import type { HRReportsPageProps } from '../types';

export function HRReportHeroCard({ meta }: { meta: HRReportsPageProps['meta'] }) {
    return (
        <Card className="overflow-hidden">
            <CardHeader className="space-y-3">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <CardTitle className="flex items-center gap-2 text-2xl">
                            <FileText className="size-6 text-cyan-500" aria-hidden="true" />
                            HR Reports
                        </CardTitle>
                        <CardDescription className="mt-1 max-w-3xl">
                            Laporan ringkas untuk membaca kondisi employee, kontrak, dan dokumen HR. Halaman ini aman untuk review karena tidak
                            menyediakan tombol create, update, delete, archive, restore, approve, cancel, atau export.
                        </CardDescription>
                    </div>
                    <Badge variant="secondary" className="gap-1">
                        <ShieldCheck className="size-3.5" aria-hidden="true" />
                        Read-only
                    </Badge>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                    {meta.scope.map((item) => (
                        <div key={item} className="bg-muted/40 rounded-xl border px-3 py-2 text-sm font-medium">
                            {item}
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
