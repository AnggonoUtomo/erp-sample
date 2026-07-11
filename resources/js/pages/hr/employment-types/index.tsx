import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteEmploymentTypeDialog } from './employment-type-components/delete-employment-type-dialog';
import { EmploymentTypeShortcutPanel } from './employment-type-components/employment-type-shortcut-panel';
import { EmploymentTypeSummaryCards } from './employment-type-components/employment-type-summary-cards';
import { EmploymentTypeTable } from './employment-type-components/employment-type-table';
import { EmploymentTypeWorkspaceCard, type EmploymentTypeWorkspaceMode } from './employment-type-components/employment-type-workspace-card';
import type { EmploymentTypeForm, EmploymentTypePageProps, EmploymentTypeRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Employment Types',
        href: '/hr/employment-types',
    },
];

function emptyForm(): EmploymentTypeForm {
    return {
        code: '',
        name: '',
        description: '',
        requires_contract_end_date: false,
        included_in_payroll: true,
        eligible_for_benefits: true,
        eligible_for_overtime: true,
        active: true,
    };
}

function toForm(row: EmploymentTypeRow): EmploymentTypeForm {
    return {
        code: row.code,
        name: row.name,
        description: row.description ?? '',
        requires_contract_end_date: row.requires_contract_end_date,
        included_in_payroll: row.included_in_payroll,
        eligible_for_benefits: row.eligible_for_benefits,
        eligible_for_overtime: row.eligible_for_overtime,
        active: row.active,
    };
}

export default function EmploymentTypesIndex({ employmentTypes, filters, summary }: EmploymentTypePageProps) {
    const { canAny } = usePermission();
    const [selectedEmploymentType, setSelectedEmploymentType] = useState<EmploymentTypeRow | null>(null);
    const [editing, setEditing] = useState<EmploymentTypeRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<EmploymentTypeRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<EmploymentTypeWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['employment-types.create', 'employment-types.manage']);
    const canUpdate = canAny(['employment-types.update', 'employment-types.manage']);
    const canDelete = canAny(['employment-types.delete', 'employment-types.manage']);
    const canRestore = canAny(['employment-types.restore', 'employment-types.manage']);
    const canForceDelete = canAny(['employment-types.force-delete', 'employment-types.manage']);

    const form = useForm<EmploymentTypeForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedEmploymentType(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectEmploymentType = (row: EmploymentTypeRow) => {
        setSelectedEmploymentType(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: EmploymentTypeRow) => {
        setSelectedEmploymentType(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.employment-types.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedEmploymentType(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.employment-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedEmploymentType(null);
                resetEditor();
            },
        });
    };

    const destroyEmploymentType = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.employment-types.force-destroy' : 'hr.employment-types.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmploymentType?.id === deleteTarget.id) {
                    setSelectedEmploymentType(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreEmploymentType = (row: EmploymentTypeRow) => {
        restoreForm.patch(route('hr.employment-types.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmploymentType?.id === row.id) {
                    setSelectedEmploymentType(null);
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
                route('hr.employment-types.index'),
                { search, status, archive, per_page: employmentTypes.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, employmentTypes.per_page, search, status]);

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
                document.getElementById('employment-type-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('employment-type-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('employment-type-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('employment-type-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('employment-types-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#employment-types-pagination-next:not(:disabled), #employment-types-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('employment-type-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedEmploymentType && canDelete && !selectedEmploymentType.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedEmploymentType);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedEmploymentType, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employment Types" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Employment Types</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola tipe hubungan kerja untuk kontrak, benefit, overtime, dan payroll employee.
                    </p>
                </div>

                <EmploymentTypeSummaryCards summary={summary} />
                <EmploymentTypeShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <EmploymentTypeTable
                                employmentTypes={employmentTypes}
                                search={search}
                                status={status}
                                archive={archive}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                canRestore={canRestore}
                                canForceDelete={canForceDelete}
                                onSearchChange={setSearch}
                                onStatusChange={setStatus}
                                onArchiveChange={setArchive}
                                onAdd={startCreate}
                                onSelect={selectEmploymentType}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreEmploymentType}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <EmploymentTypeWorkspaceCard
                            mode={workspaceMode}
                            employmentType={workspaceMode === 'edit' ? editing : selectedEmploymentType}
                            form={form}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteEmploymentTypeDialog
                employmentType={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyEmploymentType}
            />
        </AppLayout>
    );
}
