import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteEmploymentStatusDialog } from './employment-status-components/delete-employment-status-dialog';
import { EmploymentStatusShortcutPanel } from './employment-status-components/employment-status-shortcut-panel';
import { EmploymentStatusSummaryCards } from './employment-status-components/employment-status-summary-cards';
import { EmploymentStatusTable } from './employment-status-components/employment-status-table';
import { EmploymentStatusWorkspaceCard, type EmploymentStatusWorkspaceMode } from './employment-status-components/employment-status-workspace-card';
import type { EmploymentStatusForm, EmploymentStatusPageProps, EmploymentStatusRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Employment Statuses',
        href: '/hr/employment-statuses',
    },
];

function emptyForm(): EmploymentStatusForm {
    return {
        code: '',
        name: '',
        description: '',
        requires_attendance: true,
        included_in_payroll: true,
        is_final_status: false,
        active: true,
    };
}

function toForm(row: EmploymentStatusRow): EmploymentStatusForm {
    return {
        code: row.code,
        name: row.name,
        description: row.description ?? '',
        requires_attendance: row.requires_attendance,
        included_in_payroll: row.included_in_payroll,
        is_final_status: row.is_final_status,
        active: row.active,
    };
}

export default function EmploymentStatusesIndex({ employmentStatuses, filters, summary }: EmploymentStatusPageProps) {
    const { canAny } = usePermission();
    const [selectedEmploymentStatus, setSelectedEmploymentStatus] = useState<EmploymentStatusRow | null>(null);
    const [editing, setEditing] = useState<EmploymentStatusRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<EmploymentStatusRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<EmploymentStatusWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['employment-statuses.create', 'employment-statuses.manage']);
    const canUpdate = canAny(['employment-statuses.update', 'employment-statuses.manage']);
    const canDelete = canAny(['employment-statuses.delete', 'employment-statuses.manage']);
    const canRestore = canAny(['employment-statuses.restore', 'employment-statuses.manage']);
    const canForceDelete = canAny(['employment-statuses.force-delete', 'employment-statuses.manage']);

    const form = useForm<EmploymentStatusForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedEmploymentStatus(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectEmploymentStatus = (row: EmploymentStatusRow) => {
        setSelectedEmploymentStatus(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: EmploymentStatusRow) => {
        setSelectedEmploymentStatus(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.employment-statuses.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedEmploymentStatus(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.employment-statuses.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedEmploymentStatus(null);
                resetEditor();
            },
        });
    };

    const destroyEmploymentStatus = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.employment-statuses.force-destroy' : 'hr.employment-statuses.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmploymentStatus?.id === deleteTarget.id) {
                    setSelectedEmploymentStatus(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreEmploymentStatus = (row: EmploymentStatusRow) => {
        restoreForm.patch(route('hr.employment-statuses.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmploymentStatus?.id === row.id) {
                    setSelectedEmploymentStatus(null);
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
                route('hr.employment-statuses.index'),
                { search, status, archive, per_page: employmentStatuses.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, employmentStatuses.per_page, search, status]);

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
                document.getElementById('employment-status-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('employment-status-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('employment-status-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('employment-status-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('employment-statuses-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#employment-statuses-pagination-next:not(:disabled), #employment-statuses-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('employment-status-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedEmploymentStatus && canDelete && !selectedEmploymentStatus.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedEmploymentStatus);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedEmploymentStatus, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employment Statuses" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Employment Statuses</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola status kerja untuk lifecycle employee, eligibility attendance, dan inclusion payroll.
                    </p>
                </div>

                <EmploymentStatusSummaryCards summary={summary} />
                <EmploymentStatusShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <EmploymentStatusTable
                                employmentStatuses={employmentStatuses}
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
                                onSelect={selectEmploymentStatus}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreEmploymentStatus}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <EmploymentStatusWorkspaceCard
                            mode={workspaceMode}
                            employmentStatus={workspaceMode === 'edit' ? editing : selectedEmploymentStatus}
                            form={form}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteEmploymentStatusDialog
                employmentStatus={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyEmploymentStatus}
            />
        </AppLayout>
    );
}
