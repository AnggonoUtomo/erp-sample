import { Card, CardContent } from '@/components/ui/card';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import type { WorkLocationForm, WorkLocationPageProps, WorkLocationRow } from './types';
import { DeleteWorkLocationDialog } from './work-location-components/delete-work-location-dialog';
import { WorkLocationDetailCard } from './work-location-components/work-location-detail-card';
import { WorkLocationShortcutPanel } from './work-location-components/work-location-shortcut-panel';
import { WorkLocationSummaryCards } from './work-location-components/work-location-summary-cards';
import { WorkLocationTable } from './work-location-components/work-location-table';
import { WorkLocationWorkspaceCard, type WorkLocationWorkspaceMode } from './work-location-components/work-location-workspace-card';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'HR',
        href: '/hr/dashboard',
    },
    {
        title: 'Work Locations',
        href: '/hr/work-locations',
    },
];

function emptyForm(): WorkLocationForm {
    return {
        code: '',
        name: '',
        address: '',
        city: '',
        province: '',
        country: 'Indonesia',
        postal_code: '',
        timezone: 'Asia/Jakarta',
        latitude: '',
        longitude: '',
        geofence_radius_meters: '100',
        description: '',
        active: true,
    };
}

function toForm(row: WorkLocationRow): WorkLocationForm {
    return {
        code: row.code,
        name: row.name,
        address: row.address ?? '',
        city: row.city ?? '',
        province: row.province ?? '',
        country: row.country,
        postal_code: row.postal_code ?? '',
        timezone: row.timezone,
        latitude: row.latitude !== null ? String(row.latitude) : '',
        longitude: row.longitude !== null ? String(row.longitude) : '',
        geofence_radius_meters: row.geofence_radius_meters !== null ? String(row.geofence_radius_meters) : '',
        description: row.description ?? '',
        active: row.active,
    };
}

export default function WorkLocationsIndex({ workLocations, cityOptions, filters, summary, mapSettings }: WorkLocationPageProps) {
    const { canAny } = usePermission();
    const [selectedWorkLocation, setSelectedWorkLocation] = useState<WorkLocationRow | null>(null);
    const [editing, setEditing] = useState<WorkLocationRow | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<WorkLocationRow | null>(null);
    const [workspaceMode, setWorkspaceMode] = useState<WorkLocationWorkspaceMode>('detail');
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [archive, setArchive] = useState(filters.archive ?? 'active');
    const [city, setCity] = useState(filters.city ?? 'all');
    const filterMounted = useRef(false);

    const canCreate = canAny(['work-locations.create', 'work-locations.manage']);
    const canUpdate = canAny(['work-locations.update', 'work-locations.manage']);
    const canDelete = canAny(['work-locations.delete', 'work-locations.manage']);
    const canRestore = canAny(['work-locations.restore', 'work-locations.manage']);
    const canForceDelete = canAny(['work-locations.force-delete', 'work-locations.manage']);

    const form = useForm<WorkLocationForm>(emptyForm());
    const deleteForm = useForm<Record<string, never>>({});
    const restoreForm = useForm<Record<string, never>>({});

    const resetEditor = useCallback(() => {
        setEditing(null);
        setWorkspaceMode('detail');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const startCreate = useCallback(() => {
        setSelectedWorkLocation(null);
        setEditing(null);
        setWorkspaceMode('create');
        form.setData(emptyForm());
        form.clearErrors();
    }, [form]);

    const selectWorkLocation = (row: WorkLocationRow) => {
        setSelectedWorkLocation(row);
        setEditing(null);
        setWorkspaceMode('detail');
    };

    const startEdit = (row: WorkLocationRow) => {
        setSelectedWorkLocation(row);
        setEditing(row);
        setWorkspaceMode('edit');
        form.setData(toForm(row));
        form.clearErrors();
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editing) {
            form.put(route('hr.work-locations.update', editing.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedWorkLocation(null);
                    resetEditor();
                },
            });

            return;
        }

        form.post(route('hr.work-locations.store'), {
            preserveScroll: true,
            onSuccess: () => {
                setSelectedWorkLocation(null);
                resetEditor();
            },
        });
    };

    const destroyWorkLocation = () => {
        if (!deleteTarget) {
            return;
        }

        const routeName = deleteTarget.deleted_at ? 'hr.work-locations.force-destroy' : 'hr.work-locations.destroy';

        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedWorkLocation?.id === deleteTarget.id) {
                    setSelectedWorkLocation(null);
                }

                setDeleteTarget(null);
            },
        });
    };

    const restoreWorkLocation = (row: WorkLocationRow) => {
        restoreForm.patch(route('hr.work-locations.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedWorkLocation?.id === row.id) {
                    setSelectedWorkLocation(null);
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
                route('hr.work-locations.index'),
                { search, status, archive, city, per_page: workLocations.per_page },
                { preserveScroll: true, preserveState: true, replace: true },
            );
        }, 400);

        return () => window.clearTimeout(timeout);
    }, [archive, city, search, status, workLocations.per_page]);

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
                document.getElementById('work-location-search-input')?.focus();
                return;
            }

            if (event.key === '/' && !isEditableTarget(event.target)) {
                event.preventDefault();
                document.getElementById('work-location-search-input')?.focus();
                return;
            }

            if (event.altKey && key === 'c') {
                event.preventDefault();
                document.getElementById('work-location-city-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 's') {
                event.preventDefault();
                document.getElementById('work-location-status-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'a') {
                event.preventDefault();
                document.getElementById('work-location-archive-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'r') {
                event.preventDefault();
                document.getElementById('work-locations-rows-filter-trigger')?.focus();
                return;
            }

            if (event.altKey && key === 'p') {
                event.preventDefault();
                const target = document.querySelector<HTMLButtonElement>(
                    '#work-locations-pagination-next:not(:disabled), #work-locations-pagination-previous:not(:disabled)',
                );
                target?.focus();
                return;
            }

            if (event.altKey && key === 't') {
                event.preventDefault();
                document.getElementById('work-location-table-row-0')?.focus();
                return;
            }

            if (event.key === 'Delete' && selectedWorkLocation && canDelete && !selectedWorkLocation.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedWorkLocation);
                return;
            }

            if (event.key === 'Escape') {
                resetEditor();
            }
        };

        window.addEventListener('keydown', handleShortcut);

        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedWorkLocation, startCreate]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Work Locations" />

            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Work Locations</h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Kelola lokasi kerja yang menjadi referensi employee profile, attendance area, payroll, dan laporan organisasi.
                    </p>
                </div>

                <WorkLocationSummaryCards summary={summary} />
                <WorkLocationShortcutPanel />

                <div className="grid w-full min-w-0 grid-cols-1 gap-6 xl:grid-cols-3">
                    <div className="w-full min-w-0 space-y-6 xl:col-span-2">
                        {workspaceMode !== 'detail' && (
                            <WorkLocationWorkspaceCard
                                mode={workspaceMode}
                                workLocation={workspaceMode === 'edit' ? editing : null}
                                form={form}
                                canCreate={canCreate}
                                canUpdate={canUpdate}
                                mapSettings={mapSettings}
                                onSubmit={submit}
                                onCancel={resetEditor}
                            />
                        )}

                        <Card data-dashboard-card className="w-full min-w-0 overflow-hidden">
                            <CardContent className="w-full min-w-0 overflow-hidden p-5">
                                <WorkLocationTable
                                    workLocations={workLocations}
                                    cityOptions={cityOptions}
                                    search={search}
                                    status={status}
                                    archive={archive}
                                    city={city}
                                    canCreate={canCreate}
                                    canUpdate={canUpdate}
                                    canDelete={canDelete}
                                    canRestore={canRestore}
                                    canForceDelete={canForceDelete}
                                    onSearchChange={setSearch}
                                    onStatusChange={setStatus}
                                    onArchiveChange={setArchive}
                                    onCityChange={setCity}
                                    onAdd={startCreate}
                                    onSelect={selectWorkLocation}
                                    onEdit={startEdit}
                                    onDelete={setDeleteTarget}
                                    onRestore={restoreWorkLocation}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    <div className="min-w-0 space-y-6 xl:col-span-1">
                        <WorkLocationDetailCard workLocation={selectedWorkLocation ?? editing} />
                    </div>
                </div>
            </div>

            <DeleteWorkLocationDialog
                workLocation={deleteTarget}
                form={deleteForm}
                permanent={Boolean(deleteTarget?.deleted_at)}
                onOpenChange={(open) => {
                    if (!open) {
                        setDeleteTarget(null);
                    }
                }}
                onConfirm={destroyWorkLocation}
            />
        </AppLayout>
    );
}
