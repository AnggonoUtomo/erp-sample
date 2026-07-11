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
                <section className="border-sidebar-border/70 bg-card dark:border-sidebar-border rounded-2xl border p-6 shadow-sm">
                    <div className="flex items-start gap-4">
                        <div className="bg-primary/10 text-primary rounded-2xl p-3">
                            <Boxes className="h-6 w-6" />
                        </div>
                        <div>
                            <p className="text-muted-foreground text-sm font-medium">Generated module</p>
                            <h1 className="text-foreground mt-1 text-2xl font-semibold tracking-tight">H R Reference Data</h1>
                            <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                                Halaman awal module sudah siap. Lanjutkan dengan memecah komponen UI, DTO, service, policy, request, dan transaction
                                sesuai kebutuhan fitur.
                            </p>
                        </div>
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
