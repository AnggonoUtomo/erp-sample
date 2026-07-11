import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import { DeleteOrganizationStructureDialog } from './organization-structure-components/delete-organization-structure-dialog';
import { OrganizationStructureShortcutPanel } from './organization-structure-components/organization-structure-shortcut-panel';
import { OrganizationStructureSummaryCards } from './organization-structure-components/organization-structure-summary-cards';
import { OrganizationStructureTable } from './organization-structure-components/organization-structure-table';
import {
    OrganizationStructureWorkspaceCard,
    type OrganizationStructureWorkspaceMode,
} from './organization-structure-components/organization-structure-workspace-card';
import type { OrganizationStructureForm, OrganizationStructurePageProps, OrganizationStructureRow } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/dashboard' },
    { title: 'Organization Structures', href: '/hr/organization-structures' },
];
const emptyForm = (): OrganizationStructureForm => ({
    parent_id: '',
    departement_id: '',
    position_id: '',
    code: '',
    name: '',
    node_type: 'unit',
    description: '',
    active: true,
});
const toForm = (row: OrganizationStructureRow): OrganizationStructureForm => ({
    parent_id: row.parent_id ? String(row.parent_id) : '',
    departement_id: row.departement_id ? String(row.departement_id) : '',
    position_id: row.position_id ? String(row.position_id) : '',
    code: row.code,
    name: row.name,
    node_type: row.node_type,
    description: row.description ?? '',
    active: row.active,
});
const payload = (data: OrganizationStructureForm) => ({
    ...data,
    parent_id: data.parent_id || null,
    departement_id: data.departement_id || null,
    position_id: data.position_id || null,
});

export default function OrganizationStructuresIndex({
    organizationStructures,
    parentOptions,
    departementOptions,
    positionOptions,
    nodeTypeOptions,
    filters,
    summary,
}: OrganizationStructurePageProps) {
    const { canAny } = usePermission();
    const [selected, setSelected] = useState<OrganizationStructureRow | null>(null);
    const [editing, setEditing] = useState<OrganizationStructureRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<OrganizationStructureRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<OrganizationStructureWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [nodeType, setNodeType] = useState(filters.node_type ?? 'all');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const filterMounted = useRef(false);

    const canCreate = canAny(['organization-structures.create', 'organization-structures.manage']);
    const canUpdate = canAny(['organization-structures.update', 'organization-structures.manage']);
    const canDelete = canAny(['organization-structures.delete', 'organization-structures.manage']);
    const canRestore = canAny(['organization-structures.restore', 'organization-structures.manage']);
    const canForceDelete = canAny(['organization-structures.force-delete', 'organization-structures.manage']);
    const form = useForm<OrganizationStructureForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);
    const startCreate = useCallback(() => {
        setSelected(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);
    const selectRow = (row: OrganizationStructureRow) => {
        setSelected(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };
    const startEdit = (row: OrganizationStructureRow) => {
        setSelected(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.transform(payload);
        if (editing) {
            form.put(route('hr.organization-structures.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelected(null);
                    resetEditor();
                },
            });
            return;
        }
        form.post(route('hr.organization-structures.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelected(null);
                resetEditor();
            },
        });
    };

    const destroyRow = () => {
        if (!deleteTarget) return;
        const routeName = deleteTarget.deleted_at ? 'hr.organization-structures.force-destroy' : 'hr.organization-structures.destroy';
        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selected?.id === deleteTarget.id) setSelected(null);
                setDeleteTarget(null);
            },
        });
    };
    const restoreRow = (row: OrganizationStructureRow) =>
        restoreForm.patch(route('hr.organization-structures.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selected?.id === row.id) setSelected(null);
            },
        });

    useEffect(() => {
        if (!filterMounted.current) {
            filterMounted.current = true;
            return;
        }
        const timeout = window.setTimeout(
            () =>
                router.get(
                    route('hr.organization-structures.index'),
                    { search, node_type: nodeType, status, archive, per_page: organizationStructures.per_page },
                    { preserveScroll: true, preserveState: true, replace: true },
                ),
            400,
        );
        return () => window.clearTimeout(timeout);
    }, [archive, nodeType, organizationStructures.per_page, search, status]);

    useEffect(() => {
        const isEditable = (target: EventTarget | null) =>
            target instanceof HTMLElement && (['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable);
        const handle = (event: KeyboardEvent) => {
            const key = event.key.toLowerCase();
            if ((event.metaKey || event.ctrlKey) && event.shiftKey && key === 'a' && canCreate) {
                event.preventDefault();
                startCreate();
                return;
            }
            if ((event.metaKey || event.ctrlKey) && key === 'k') {
                event.preventDefault();
                document.getElementById('organization-structure-search-input')?.focus();
                return;
            }
            if (event.key === '/' && !isEditable(event.target)) {
                event.preventDefault();
                document.getElementById('organization-structure-search-input')?.focus();
                return;
            }
            if (event.altKey && key === 'n') {
                event.preventDefault();
                document.getElementById('organization-structure-node-type-filter-trigger')?.focus();
                return;
            }
            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('organization-structure-status-filter-trigger')?.focus();
                return;
            }
            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('organization-structure-archive-filter-trigger')?.focus();
                return;
            }
            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('organization-structure-rows-filter-trigger')?.focus();
                return;
            }
            if (event.altKey && key === 'p') {
                event.preventDefault();
                document
                    .querySelector<HTMLButtonElement>(
                        '#organization-structure-pagination-next:not(:disabled), #organization-structure-pagination-previous:not(:disabled)',
                    )
                    ?.focus();
                return;
            }
            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('organization-structure-table-row-0')?.focus();
                return;
            }
            if (event.key === 'Delete' && selected && canDelete && !selected.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selected);
                return;
            }
            if (event.key === 'Escape') resetEditor();
        };
        window.addEventListener('keydown', handle);
        return () => window.removeEventListener('keydown', handle);
    }, [canCreate, canDelete, resetEditor, selected, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Organization Structures" />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Organization Structures</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola hierarchy organisasi untuk reporting line, supervisor relationship, dan approval.
                    </p>
                </div>
                <OrganizationStructureSummaryCards summary={summary} />
                <OrganizationStructureShortcutPanel />
                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <Card data-dashboard-card className="w-full min-w-0 overflow-hidden xl:col-span-2">
                        <CardContent className="w-full min-w-0 overflow-hidden p-5">
                            <OrganizationStructureTable
                                organizationStructures={organizationStructures}
                                nodeTypeOptions={nodeTypeOptions}
                                search={search}
                                nodeType={nodeType}
                                status={status}
                                archive={archive}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                canDelete={canDelete}
                                canRestore={canRestore}
                                canForceDelete={canForceDelete}
                                onSearchChange={setSearch}
                                onNodeTypeChange={setNodeType}
                                onStatusChange={setStatus}
                                onArchiveChange={setArchive}
                                onAdd={startCreate}
                                onSelect={selectRow}
                                onEdit={startEdit}
                                onDelete={setDeleteTarget}
                                onRestore={restoreRow}
                            />
                        </CardContent>
                    </Card>
                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <OrganizationStructureWorkspaceCard
                            mode={workspaceMode}
                            organizationStructure={workspaceMode === 'edit' ? editing : selected}
                            form={form}
                            parentOptions={parentOptions}
                            departementOptions={departementOptions}
                            positionOptions={positionOptions}
                            nodeTypeOptions={nodeTypeOptions}
                            canCreate={canCreate}
                            canUpdate={canUpdate}
                            onSubmit={submit}
                            onCancel={resetEditor}
                        />
                    </div>
                </div>
            </div>
            <DeleteOrganizationStructureDialog
                organizationStructure={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) setDeleteTarget(null);
                }}
                onConfirm={destroyRow}
            />
        </AppLayout>
    );
}
