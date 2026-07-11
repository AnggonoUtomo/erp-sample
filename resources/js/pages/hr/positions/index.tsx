import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeletePositionDialog } from './position-components/delete-position-dialog';
import { PositionShortcutPanel } from './position-components/position-shortcut-panel';
import { PositionSummaryCards } from './position-components/position-summary-cards';
import { PositionTable } from './position-components/position-table';
import { PositionWorkspaceCard, type PositionWorkspaceMode } from './position-components/position-workspace-card';
import type { PositionForm, PositionPageProps, PositionRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Positions',
        href: '/hr/positions',
    },
];

function emptyForm(): PositionForm {
    return {
        departement_id: '',
        code: '',
        name: '',
        description: '',
        active: true,
    };
}

function toForm(row: PositionRow): PositionForm {
    return {
        departement_id: String(row.departement_id),
        code: row.code,
        name: row.name,
        description: row.description ?? '',
        active: row.active,
    };
}

export default function PositionsIndex({ positions, departementOptions, filters, summary }: PositionPageProps) {
    const { canAny } = usePermission();
    const [selectedPosition, setSelectedPosition] = useState<PositionRow | null>(null);
    const [editing, setEditing] = useState<PositionRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<PositionRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<PositionWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [departement, setDepartement] = useState(filters.departement ?? 'all');
    const filterMounted = useRef(false);

    const canCreate = canAny(['positions.create', 'positions.manage']);
    const canUpdate = canAny(['positions.update', 'positions.manage']);
    const canDelete = canAny(['positions.delete', 'positions.manage']);

    const form = useForm<PositionForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedPosition(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectPosition = (row: PositionRow) => {
        setSelectedPosition(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: PositionRow) => {
        setSelectedPosition(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.positions.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedPosition(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.positions.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedPosition(null);
                resetEditor();
            },
        });
    };

    const destroyPosition = () => {
        if (!deleteTarget) {
            return;
        }

        deleteForm.delete(route('hr.positions.destroy', deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedPosition?.id === deleteTarget.id) {
                    setSelectedPosition(null);
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
                route('hr.positions.index'),
                { search, status, departement, per_page: positions.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [departement, positions.per_page, search, status]);

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
                document.getElementById('position-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('position-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 'd') {
                event.preventDefault();
                document.getElementById('position-departement-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('position-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('positions-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#positions-pagination-next:not(:disabled), #positions-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('position-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedPosition && canDelete) {
                event.preventDefault();
                setDeleteTarget(selectedPosition);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedPosition, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Positions" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Positions</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola master jabatan HR yang menjadi referensi employee profile, approval flow, attendance, dan payroll.
                    </p>
                </div>

                <PositionSummaryCards summary={summary} />
                <PositionShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <PositionTable
                                positions={positions}
                                departementOptions={departementOptions}
                                search={search}
                                status={status}
                                departement={departement}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                onSearchChange={setSearch}
                                onStatusChange={setStatus}
                                onDepartementChange={setDepartement}
                                onAdd={startCreate}
                                onSelect={selectPosition}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                            />
                        </CardContent>
                    </Card>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <PositionWorkspaceCard
                            mode={workspaceMode}
                            position={workspaceMode === 'edit' ? editing : selectedPosition}
                            form={form}
                            departementOptions={departementOptions}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>

            <DeletePositionDialog
                position={deleteTarget}
                form={deleteForm}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyPosition}
            />
        </AppLayout>
    );
}
