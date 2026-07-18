import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { FileText, ShieldCheck } from 'lucide-react';

interface HRReportsPageProps {
    meta: {
        status: 'read-only-boundary';
        scope: string[];
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'HR Reports',
        href: '/hr/reports',
    },
];

export default function HRReportsIndex({ meta }: HRReportsPageProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HR Reports" />

            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <Card className="border-dashed">
                    <CardHeader className="space-y-3">
                        <div className="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <CardTitle className="flex items-center gap-2">
                                    <FileText className="size-5 text-cyan-500" />
                                    HR Reports
                                </CardTitle>
                                <CardDescription>
                                    Boundary awal laporan HR. Module ini hanya membaca data dan belum membuka action mutasi atau export.
                                </CardDescription>
                            </div>
                            <Badge variant="secondary" className="gap-1">
                                <ShieldCheck className="size-3.5" />
                                {meta.status}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        {meta.scope.map((item) => (
                            <div key={item} className="bg-muted/30 rounded-xl border p-3 text-sm">
                                {item}
                            </div>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
