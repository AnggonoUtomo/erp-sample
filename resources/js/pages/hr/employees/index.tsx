import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteEmployeeDialog } from './employee-components/delete-employee-dialog';
import { EmployeeShortcutPanel } from './employee-components/employee-shortcut-panel';
import { EmployeeSummaryCards } from './employee-components/employee-summary-cards';
import { EmployeeTable } from './employee-components/employee-table';
import { EmployeeWorkspaceCard, type EmployeeWorkspaceMode } from './employee-components/employee-workspace-card';
import type { EmployeeForm, EmployeePageProps, EmployeeRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Employees',
        href: '/hr/employees',
    },
];

function emptyForm(): EmployeeForm {
    return {
        user_id: '',
        departement_id: '',
        position_id: '',
        job_level_id: '',
        work_location_id: '',
        employment_status_id: '',
        employment_type_id: '',
        employee_number: '',
        first_name: '',
        last_name: '',
        display_name: '',
        work_email: '',
        personal_email: '',
        phone: '',
        hired_at: '',
        ended_at: '',
        notes: '',
        active: true,
        avatar: null,
        remove_avatar: false,
    };
}

function toForm(row: EmployeeRow): EmployeeForm {
    return {
        user_id: row.user_id ? String(row.user_id) : '',
        departement_id: row.departement_id ? String(row.departement_id) : '',
        position_id: row.position_id ? String(row.position_id) : '',
        job_level_id: row.job_level_id ? String(row.job_level_id) : '',
        work_location_id: row.work_location_id ? String(row.work_location_id) : '',
        employment_status_id: row.employment_status_id ? String(row.employment_status_id) : '',
        employment_type_id: row.employment_type_id ? String(row.employment_type_id) : '',
        employee_number: row.employee_number,
        first_name: row.first_name,
        last_name: row.last_name ?? '',
        display_name: row.display_name,
        work_email: row.work_email ?? '',
        personal_email: row.personal_email ?? '',
        phone: row.phone ?? '',
        hired_at: row.hired_at ?? '',
        ended_at: row.ended_at ?? '',
        notes: row.notes ?? '',
        active: row.active,
        avatar: null,
        remove_avatar: false,
    };
}

function nullable(value: string) {
    return value === '' ? null : value;
}

function payload(data: EmployeeForm) {
    return {
        user_id: nullable(data.user_id),
        departement_id: nullable(data.departement_id),
        position_id: nullable(data.position_id),
        job_level_id: nullable(data.job_level_id),
        work_location_id: nullable(data.work_location_id),
        employment_status_id: nullable(data.employment_status_id),
        employment_type_id: nullable(data.employment_type_id),
        employee_number: data.employee_number,
        first_name: data.first_name,
        last_name: data.last_name,
        display_name: data.display_name,
        work_email: data.work_email,
        personal_email: data.personal_email,
        phone: data.phone,
        hired_at: data.hired_at,
        ended_at: data.ended_at,
        notes: data.notes,
        active: data.active,
        avatar: data.avatar,
        remove_avatar: data.remove_avatar,
    };
}

export default function EmployeesIndex({ employees, options, filters, summary }: EmployeePageProps) {
    const { canAny } = usePermission();
    const [selectedEmployee, setSelectedEmployee] = useState<EmployeeRow | null>(null);
    const [editing, setEditing] = useState<EmployeeRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<EmployeeRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<EmployeeWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [departement, setDepartement] = useState(filters.departement ?? 'all');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['employees.create', 'employees.manage']);
    const canUpdate = canAny(['employees.update', 'employees.manage']);
    const canDelete = canAny(['employees.delete', 'employees.manage']);
    const canRestore = canAny(['employees.restore', 'employees.manage']);
    const canForceDelete = canAny(['employees.force-delete', 'employees.manage']);

    const form = useForm<EmployeeForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedEmployee(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectEmployee = (row: EmployeeRow) => {
        setSelectedEmployee(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: EmployeeRow) => {
        setSelectedEmployee(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const data = payload(form.data);

        if (editing) {
            router.post(route('hr.employees.update', editing.id), data, {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedEmployee(null);
                    resetEditor();
                },
            });

            return;
        }

        router.post(route('hr.employees.store'), data, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                setSelectedEmployee(null);
                resetEditor();
            },
        });
    };

    const destroyEmployee = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.employees.force-destroy' : 'hr.employees.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmployee?.id === deleteTarget.id) {
                    setSelectedEmployee(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreEmployee = (row: EmployeeRow) => {
        restoreForm.patch(route('hr.employees.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedEmployee?.id === row.id) {
                    setSelectedEmployee(null);
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
                route('hr.employees.index'),
                { search, departement, status, archive, per_page: employees.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, departement, employees.per_page, search, status]);

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
                document.getElementById('employee-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('employee-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 'd') {
                event.preventDefault();
                document.getElementById('employee-departement-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('employee-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('employee-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('employee-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#employee-pagination-next:not(:disabled), #employee-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('employee-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedEmployee && canDelete && !selectedEmployee.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedEmployee);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedEmployee, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employees" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Employees</h1>
                    <p className="mt-1 text-sm text-muted-foreground">Kelola master employee, avatar, user login, struktur organisasi, dan data kerja inti HR.</p>
                </div>

                <EmployeeSummaryCards summary={summary} />
                <EmployeeShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <EmployeeTable
                                employees={employees}
                                departementOptions={options.departements}
                                search={search}
                                departement={departement}
                                status={status}
                                archive={archive}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                canRestore={canRestore}
                                canForceDelete={canForceDelete}
                                onSearchChange={setSearch}
                                onDepartementChange={setDepartement}
                                onStatusChange={setStatus}
                                onArchiveChange={setArchive}
                                onAdd={startCreate}
                                onSelect={selectEmployee}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreEmployee}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 xl:col-span-1">
                        <EmployeeWorkspaceCard
                            mode={workspaceMode}
                            employee={workspaceMode === 'edit' ? editing : selectedEmployee}
                            form={form}
                            options={options}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteEmployeeDialog
                employee={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyEmployee}
            />
        </AppLayout>
    );
}
