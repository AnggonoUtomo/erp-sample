import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteJobLevelDialog } from './job-level-components/delete-job-level-dialog';
import { JobLevelShortcutPanel } from './job-level-components/job-level-shortcut-panel';
import { JobLevelSummaryCards } from './job-level-components/job-level-summary-cards';
import { JobLevelTable } from './job-level-components/job-level-table';
import { JobLevelWorkspaceCard, type JobLevelWorkspaceMode } from './job-level-components/job-level-workspace-card';
import type { JobLevelForm, JobLevelPageProps, JobLevelRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Job Levels',
        href: '/hr/job-levels',
    },
];

function emptyForm(): JobLevelForm {
    return {
        code: '',
        name: '',
        description: '',
        active: true,
    };
}

function toForm(row: JobLevelRow): JobLevelForm {
    return {
        code: row.code,
        name: row.name,
        description: row.description ?? '',
        active: row.active,
    };
}

export default function JobLevelsIndex({ jobLevels, filters, summary }: JobLevelPageProps) {
    const { canAny } = usePermission();
    const [selectedJobLevel, setSelectedJobLevel] = useState<JobLevelRow | null>(null);
    const [editing, setEditing] = useState<JobLevelRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<JobLevelRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<JobLevelWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['job-levels.create', 'job-levels.manage']);
    const canUpdate = canAny(['job-levels.update', 'job-levels.manage']);
    const canDelete = canAny(['job-levels.delete', 'job-levels.manage']);
    const canRestore = canAny(['job-levels.restore', 'job-levels.manage']);
    const canForceDelete = canAny(['job-levels.force-delete', 'job-levels.manage']);

    const form = useForm<JobLevelForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedJobLevel(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectJobLevel = (row: JobLevelRow) => {
        setSelectedJobLevel(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: JobLevelRow) => {
        setSelectedJobLevel(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.job-levels.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedJobLevel(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.job-levels.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedJobLevel(null);
                resetEditor();
            },
        });
    };

    const destroyJobLevel = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.job-levels.force-destroy' : 'hr.job-levels.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedJobLevel?.id === deleteTarget.id) {
                    setSelectedJobLevel(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreJobLevel = (row: JobLevelRow) => {
        restoreForm.patch(route('hr.job-levels.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedJobLevel?.id === row.id) {
                    setSelectedJobLevel(null);
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
                route('hr.job-levels.index'),
                { search, status, archive, per_page: jobLevels.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, jobLevels.per_page, search, status]);

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
                document.getElementById('job-level-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('job-level-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('job-level-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('job-level-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('job-levels-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#job-levels-pagination-next:not(:disabled), #job-levels-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('job-level-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedJobLevel && canDelete && !selectedJobLevel.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedJobLevel);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedJobLevel, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Job Levels" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Job Levels</h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Kelola grade atau level jabatan yang menjadi referensi employee profile, approval flow, benefit, dan payroll.
                    </p>
                </div>

                <JobLevelSummaryCards summary={summary} />
                <JobLevelShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <JobLevelTable
                                jobLevels={jobLevels}
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
                                onSelect={selectJobLevel}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreJobLevel}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <JobLevelWorkspaceCard
                            mode={workspaceMode}
                            jobLevel={workspaceMode === 'edit' ? editing : selectedJobLevel}
                            form={form}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeleteJobLevelDialog
                jobLevel={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyJobLevel}
            />
        </AppLayout>
    );
}
