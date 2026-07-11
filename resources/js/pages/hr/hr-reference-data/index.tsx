import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { DeleteHRReferenceDataDialog } from './hr-reference-data-components/delete-hr-reference-data-dialog';
import { HRReferenceDataShortcutPanel } from './hr-reference-data-components/hr-reference-data-shortcut-panel';
import { HRReferenceDataSummaryCards } from './hr-reference-data-components/hr-reference-data-summary-cards';
import { HRReferenceDataTable } from './hr-reference-data-components/hr-reference-data-table';
import { HRReferenceDataWorkspaceCard } from './hr-reference-data-components/hr-reference-data-workspace-card';
import { ReferenceCategoryPanel } from './hr-reference-data-components/reference-category-panel';
import type { ReferenceDataPageProps } from './types';
import { useHRReferenceDataPage } from './use-hr-reference-data-page';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'HR Reference Data',
        href: '/hr/hr-reference-data',
    },
];

export default function HRReferenceDataIndex({ referenceData, categoryOptions, referenceCategories, filters, summary }: ReferenceDataPageProps) {
    const {
        selectedReferenceData,
        editing,
        deleteTarget,
        setDeleteTarget,
        editingCategory,
        workspaceMode,
        search,
        setSearch,
        category,
        setCategory,
        status,
        setStatus,
        archive,
        setArchive,
        canCreate,
        canUpdate,
        canDelete,
        canRestore,
        canForceDelete,
        form,
        categoryForm,
        deleteForm,
        resetEditor,
        resetCategoryEditor,
        startCreate,
        selectReferenceData,
        startEdit,
        submit,
        submitCategory,
        startEditCategory,
        deleteCategory,
        destroyReferenceData,
        restoreReferenceData,
    } = useHRReferenceDataPage({ filters, referenceData });

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="HR Reference Data" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">HR Reference Data</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola referensi umum seperti gender, marital status, education level, religion, bank, dan blood type.
                    </p>
                </div>

                <HRReferenceDataSummaryCards summary={summary} />
                <HRReferenceDataShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <HRReferenceDataTable
                                referenceData={referenceData}
                                categoryOptions={categoryOptions}
                                search={search}
                                category={category}
                                status={status}
                                archive={archive}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                canRestore={canRestore}
                                canForceDelete={canForceDelete}
                                onSearchChange={setSearch}
                                onCategoryChange={setCategory}
                                onStatusChange={setStatus}
                                onArchiveChange={setArchive}
                                onAdd={startCreate}
                                onSelect={selectReferenceData}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreReferenceData}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <HRReferenceDataWorkspaceCard
                            mode={workspaceMode}
                            referenceData={workspaceMode === 'edit' ? editing : selectedReferenceData}
                            form={form}
                            categoryOptions={categoryOptions}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                        <ReferenceCategoryPanel
                            categories={referenceCategories}
                            form={categoryForm}
                            editing={editingCategory}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            canDelete={canDelete}
                            onSubmit={submitCategory}
                            onEdit={startEditCategory}
                            onDelete={deleteCategory}
                            onCancel={resetCategoryEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteHRReferenceDataDialog
                referenceData={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyReferenceData}
            />
        </AppLayout>
    );
}
