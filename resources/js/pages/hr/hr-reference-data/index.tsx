import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteHRReferenceDataDialog } from './hr-reference-data-components/delete-hr-reference-data-dialog';
import { HRReferenceDataShortcutPanel } from './hr-reference-data-components/hr-reference-data-shortcut-panel';
import { HRReferenceDataSummaryCards } from './hr-reference-data-components/hr-reference-data-summary-cards';
import { HRReferenceDataTable } from './hr-reference-data-components/hr-reference-data-table';
import { HRReferenceDataWorkspaceCard, type HRReferenceDataWorkspaceMode } from './hr-reference-data-components/hr-reference-data-workspace-card';
import { ReferenceCategoryPanel } from './hr-reference-data-components/reference-category-panel';
import type { ReferenceCategoryForm, ReferenceCategoryRow, ReferenceDataForm, ReferenceDataPageProps, ReferenceDataRow } from './types';

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

function emptyForm(): ReferenceDataForm {
    return {
        category: '',
        code: '',
        name: '',
        description: '',
        active: true,
    };
}

function emptyCategoryForm(): ReferenceCategoryForm {
    return {
        code: '',
        name: '',
        description: '',
        active: true,
    };
}

function makeCategoryCode(name: string) {
    return name
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function toForm(row: ReferenceDataRow): ReferenceDataForm {
    return {
        category: row.category,
        code: row.code,
        name: row.name,
        description: row.description ?? '',
        active: row.active,
    };
}

function payload(data: ReferenceDataForm) {
    return {
        category: data.category,
        code: data.code,
        name: data.name,
        description: data.description,
        metadata: null,
        active: data.active,
    };
}

export default function HRReferenceDataIndex({ referenceData, categoryOptions, referenceCategories, filters, summary }: ReferenceDataPageProps) {
    const { canAny } = usePermission();
    const [selectedReferenceData, setSelectedReferenceData] = useState<ReferenceDataRow | null>(null);
    const [editing, setEditing] = useState<ReferenceDataRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<ReferenceDataRow | null>(null);
    const [editingCategory, setEditingCategory] = useState<ReferenceCategoryRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<HRReferenceDataWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [category, setCategory] = useState(filters.category ?? 'all');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['hr-reference-data.create', 'hr-reference-data.manage']);
    const canUpdate = canAny(['hr-reference-data.update', 'hr-reference-data.manage']);
    const canDelete = canAny(['hr-reference-data.delete', 'hr-reference-data.manage']);
    const canRestore = canAny(['hr-reference-data.restore', 'hr-reference-data.manage']);
    const canForceDelete = canAny(['hr-reference-data.force-delete', 'hr-reference-data.manage']);

    const form = useForm<ReferenceDataForm>(emptyForm());
    const categoryForm = useForm<ReferenceCategoryForm>(emptyCategoryForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const resetCategoryEditor = useCallback(() => {
        setEditingCategory(null);
        categoryForm.setData(emptyCategoryForm());
        categoryForm.clearErrors();
    }, [categoryForm]);

    const startCreate = useCallback(() => {
        setSelectedReferenceData(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectReferenceData = (row: ReferenceDataRow) => {
        setSelectedReferenceData(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: ReferenceDataRow) => {
        setSelectedReferenceData(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        const data = payload(form.data);

        if (editing) {
            router.put(route('hr.hr-reference-data.update', editing.id), data, {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedReferenceData(null);
                    resetEditor();
                },
            });

            return;
        }

        router.post(route('hr.hr-reference-data.store'), data, {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedReferenceData(null);
                resetEditor();
            },
        });
    };

    const submitCategory = (event: FormEvent) => {
        event.preventDefault();
        const data = {
            ...categoryForm.data,
            code: categoryForm.data.code || makeCategoryCode(categoryForm.data.name),
        };

        if (editingCategory) {
            router.put(route('hr.hr-reference-data.categories.update', editingCategory.id), data, {
                preserveScroll: true,
                onSuccess: resetCategoryEditor,
            });
            return;
        }

        router.post(route('hr.hr-reference-data.categories.store'), data, {
            preserveScroll: true,
            onSuccess: resetCategoryEditor,
        });
    };

    const startEditCategory = (category: ReferenceCategoryRow) => {
        setEditingCategory(category);
        categoryForm.setData({
            code: category.code,
            name: category.name,
            description: category.description ?? '',
            active: category.active,
        });
        categoryForm.clearErrors();
    };

    const deleteCategory = (category: ReferenceCategoryRow) => {
        if (category.items_count > 0) {
            return;
        }

        router.delete(route('hr.hr-reference-data.categories.destroy', category.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (editingCategory?.id === category.id) {
                    resetCategoryEditor();
                }
            },
        });
    };

    const destroyReferenceData = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.hr-reference-data.force-destroy' : 'hr.hr-reference-data.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedReferenceData?.id === deleteTarget.id) {
                    setSelectedReferenceData(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreReferenceData = (row: ReferenceDataRow) => {
        restoreForm.patch(route('hr.hr-reference-data.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedReferenceData?.id === row.id) {
                    setSelectedReferenceData(null);
                }
            },
        });
    };

    useEffect(() => {
        if (!filterMounted.current) {
            filterMounted.current = true;
            return;
        }

        const timeout = window.setTimeout(() => {
            router.get(
                route('hr.hr-reference-data.index'),
                { search, category, status, archive, per_page: referenceData.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, category, referenceData.per_page, search, status]);

    useEffect(() => {
        const isEditableTarget = (target: EventTarget | null) => {
            if (!(target instanceof HTMLElement)) {
                return false;
            }

            return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable;
        };

        const handleShortcut = (event: KeyboardEvent) => {
            const key = event.key.toLowerCase();

            if ((event.metaKey || event.ctrlKey) && event.shiftKey && key === 'a' && canCreate) {
                event.preventDefault();
                startCreate();
                return;
            }

            if ((event.metaKey || event.ctrlKey) && key === 'k') {
                event.preventDefault();
                document.getElementById('hr-reference-data-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('hr-reference-data-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 'c') {
                event.preventDefault();
                document.getElementById('hr-reference-data-category-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('hr-reference-data-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('hr-reference-data-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('hr-reference-data-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#hr-reference-data-pagination-next:not(:disabled), #hr-reference-data-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('hr-reference-data-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedReferenceData && canDelete && !selectedReferenceData.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedReferenceData);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedReferenceData, startCreate]);

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
