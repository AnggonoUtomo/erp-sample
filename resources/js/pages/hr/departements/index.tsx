import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { DeleteDepartementDialog } from './departement-components/delete-departement-dialog';
import { DepartementShortcutPanel } from './departement-components/departement-shortcut-panel';
import { DepartementSummaryCards } from './departement-components/departement-summary-cards';
import { DepartementTable } from './departement-components/departement-table';
import { DepartementWorkspaceCard, type DepartementWorkspaceMode } from './departement-components/departement-workspace-card';
import type { DepartementForm, DepartementPageProps, DepartementRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Departements',
        href: '/hr/departements',
    },
];

function emptyForm(): DepartementForm {
    return {
        code: '',
        name: '',
        parent_id: '',
        description: '',
        active: true,
    };
}

function toForm(row: DepartementRow): DepartementForm {
    return {
        code: row.code,
        name: row.name,
        parent_id: row.parent ? String(row.parent.id) : '',
        description: row.description ?? '',
        active: row.active,
    };
}

export default function DepartementsIndex({ departements, departementOptions, filters, summary }: DepartementPageProps) {
    const { canAny } = usePermission();
    const [selectedDepartement, setSelectedDepartement] = useState<DepartementRow | null>(null);
    const [editing, setEditing] = useState<DepartementRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<DepartementRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<DepartementWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const filterMounted = useRef(false);

    const canCreate = canAny(['departements.create', 'departements.manage']);
    const canUpdate = canAny(['departements.update', 'departements.manage']);
    const canDelete = canAny(['departements.delete', 'departements.manage']);

    const form = useForm<DepartementForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});

    const parentOptions = useMemo(
        () => departementOptions.filter((option) => !editing || option.id !== editing.id),
        [departementOptions, editing],
    );

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedDepartement(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectDepartement = (row: DepartementRow) => {
        setSelectedDepartement(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: DepartementRow) => {
        setSelectedDepartement(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.departements.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedDepartement(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.departements.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedDepartement(null);
                resetEditor();
            },
        });
    };

    const destroyDepartement = () => {
        if (!deleteTarget) {
            return;
        }

        deleteForm.delete(route('hr.departements.destroy', deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedDepartement?.id === deleteTarget.id) {
                    setSelectedDepartement(null);
                }

                setDeleteTarget(null);
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
                route('hr.departements.index'),
                { search, status, per_page: departements.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [departements.per_page, search, status]);

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
                document.getElementById('departement-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('departement-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('departement-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('departements-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#departements-pagination-next:not(:disabled), #departements-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('departement-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedDepartement && canDelete) {
                event.preventDefault();
                setDeleteTarget(selectedDepartement);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedDepartement, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Departements" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Departements</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Kelola struktur unit organisasi HR untuk employee, attendance, payroll, approval flow, dan laporan headcount.
                    </p>
                </div>

                <DepartementSummaryCards summary={summary} />
                <DepartementShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <DepartementTable
                                departements={departements}
                                search={search}
                                status={status}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                onSearchChange={setSearch}
                                onStatusChange={setStatus}
                                onAdd={startCreate}
                                onSelect={selectDepartement}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <DepartementWorkspaceCard
                            mode={workspaceMode}
                            departement={workspaceMode === 'edit' ? editing : selectedDepartement}
                            form={form}
                            parentOptions={parentOptions}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteDepartementDialog
                departement={deleteTarget}
                form={deleteForm}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyDepartement}
            />
        </AppLayout>
    );
}
