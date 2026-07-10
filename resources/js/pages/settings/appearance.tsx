import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import ThemeSelector from '@/components/theme-selector';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengaturan tampilan',
        href: '/settings/appearance',
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pengaturan tampilan" />

            <SettingsLayout>
                <div className="space-y-10">
                    <div className="space-y-4">
                        <HeadingSmall title="Mode tampilan" description="Pilih mode terang atau gelap untuk antarmuka aplikasi." />
                        <AppearanceTabs />
                    </div>

                    <div className="space-y-4">
                        <HeadingSmall title="Tema warna" description="Pilih sistem warna dashboard yang digunakan di seluruh aplikasi." />
                        <ThemeSelector />
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
