import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { type FormEvent, useMemo, useState } from 'react';
import { NotificationTemplateHeader } from './notification-template-components/notification-template-header';
import { TemplateEditorCard } from './notification-template-components/template-editor-card';
import { TemplateListCard } from './notification-template-components/template-list-card';
import type { NotificationTemplate, TemplateForm } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification Templates',
        href: '/notification-templates',
    },
];

type Props = {
    templates: NotificationTemplate[];
    can: {
        update: boolean;
    };
};

export default function NotificationTemplates({ templates, can }: Props) {
    const [selectedId, setSelectedId] = useState(templates[0]?.id ?? null);
    const selectedTemplate = useMemo(() => templates.find((template) => template.id === selectedId) ?? templates[0] ?? null, [selectedId, templates]);

    const form = useForm<TemplateForm>({
        subject: selectedTemplate?.subject ?? '',
        body: selectedTemplate?.body ?? '',
        active: selectedTemplate?.active ?? true,
    });

    const selectTemplate = (template: NotificationTemplate) => {
        setSelectedId(template.id);
        form.setData({
            subject: template.subject ?? '',
            body: template.body ?? '',
            active: template.active,
        });
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!selectedTemplate) {
            return;
        }

        form.put(route('notification-templates.update', selectedTemplate.id), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification Templates" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <NotificationTemplateHeader count={templates.length} />

                <div className="grid gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
                    <TemplateListCard templates={templates} selectedTemplate={selectedTemplate} onSelect={selectTemplate} />
                    <TemplateEditorCard selectedTemplate={selectedTemplate} canUpdate={can.update} form={form} onSubmit={submit} />
                </div>
            </div>
        </AppLayout>
    );
}
