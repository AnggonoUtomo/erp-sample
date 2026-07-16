import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { ListChecks } from 'lucide-react';
import { OffboardingTemplateForm } from './template-components/offboarding-template-form';
import { OffboardingTemplateList } from './template-components/offboarding-template-list';
import type { TemplatePaginator } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/dashboard' },
    { title: 'Offboarding Templates', href: '/hr/offboardings/templates' },
];

export default function OffboardingTemplatesIndex({ templates }: { templates: TemplatePaginator }) {
    const { canAny } = usePermission();
    const canCreate = canAny(['offboardings.template-manage', 'offboardings.manage']);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Offboarding Templates" />
            <main className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <header>
                    <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                        <ListChecks className="size-6" aria-hidden="true" />
                        Offboarding Templates
                    </h1>
                    <p className="text-muted-foreground mt-1 max-w-3xl text-sm">
                        Susun checklist standar berdasarkan exit date. Template akan disalin menjadi snapshot saat offboarding dibuat.
                    </p>
                </header>

                <div className={`grid gap-6 ${canCreate ? 'xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.8fr)]' : ''}`}>
                    <OffboardingTemplateList templates={templates} />
                    {canCreate && <OffboardingTemplateForm />}
                </div>
            </main>
        </AppLayout>
    );
}
