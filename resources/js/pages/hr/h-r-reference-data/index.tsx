import AppLayout from '@/layouts/app-layout';
import { Head } from '@inertiajs/react';
import { Boxes } from 'lucide-react';

const breadcrumbs = [
    {
        title: 'H R Reference Data',
        href: '/hr/h-r-reference-data',
    },
];

export default function HRReferenceDataIndex() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="H R Reference Data" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <section className="rounded-2xl border border-sidebar-border/70 bg-card p-6 shadow-sm dark:border-sidebar-border">
                    <div className="flex items-start gap-4">
                        <div className="rounded-2xl bg-primary/10 p-3 text-primary">
                            <Boxes className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-sm font-medium text-muted-foreground">Generated module</p>
                            <h1 className="mt-1 text-2xl font-semibold tracking-tight text-foreground">H R Reference Data</h1>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground">
                                Halaman awal module sudah siap. Lanjutkan dengan memecah komponen UI, DTO, service,
                                policy, request, dan transaction sesuai kebutuhan fitur.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}