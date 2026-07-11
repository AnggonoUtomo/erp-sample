import { usePermission } from '@/hooks/use-permission';
import { router, useForm } from '@inertiajs/react';
import { FormEvent, useCallback, useEffect, useRef, useState } from 'react';
import type { HRReferenceDataWorkspaceMode } from './hr-reference-data-components/hr-reference-data-workspace-card';
import type { ReferenceCategoryForm, ReferenceCategoryRow, ReferenceDataForm, ReferenceDataPageProps, ReferenceDataRow } from './types';

type PageState = Pick<ReferenceDataPageProps, 'filters' | 'referenceData'>;

const emptyForm = (): ReferenceDataForm => ({ category: '', code: '', name: '', description: '', active: true });
const emptyCategoryForm = (): ReferenceCategoryForm => ({ code: '', name: '', description: '', active: true });

const makeCategoryCode = (name: string) =>
    name
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const toForm = (row: ReferenceDataRow): ReferenceDataForm => ({
    category: row.category,
    code: row.code,
    name: row.name,
    description: row.description ?? '',
    active: row.active,
});

export function useHRReferenceDataPage({ filters, referenceData }: PageState) {
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
        const data = { ...form.data, metadata: null };
        const onSuccess = () => {
            setSelectedReferenceData(null);
            resetEditor();
        };

        if (editing) {
            router.put(route('hr.hr-reference-data.update', editing.id), data, { preserveScroll: true, onSuccess });
            return;
        }
        router.post(route('hr.hr-reference-data.store'), data, { preserveScroll: true, onSuccess });
    };

    const submitCategory = (event: FormEvent) => {
        event.preventDefault();
        const data = { ...categoryForm.data, code: categoryForm.data.code || makeCategoryCode(categoryForm.data.name) };
        if (editingCategory) {
            router.put(route('hr.hr-reference-data.categories.update', editingCategory.id), data, {
                preserveScroll: true,
                onSuccess: resetCategoryEditor,
            });
            return;
        }
        router.post(route('hr.hr-reference-data.categories.store'), data, { preserveScroll: true, onSuccess: resetCategoryEditor });
    };

    const startEditCategory = (item: ReferenceCategoryRow) => {
        setEditingCategory(item);
        categoryForm.setData({ code: item.code, name: item.name, description: item.description ?? '', active: item.active });
        categoryForm.clearErrors();
    };

    const deleteCategory = (item: ReferenceCategoryRow) => {
        if (item.items_count > 0) return;
        router.delete(route('hr.hr-reference-data.categories.destroy', item.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (editingCategory?.id === item.id) resetCategoryEditor();
            },
        });
    };

    const destroyReferenceData = () => {
        if (!deleteTarget) return;
        const routeName = deleteTarget.deleted_at ? 'hr.hr-reference-data.force-destroy' : 'hr.hr-reference-data.destroy';
        deleteForm.delete(route(routeName, deleteTarget.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedReferenceData?.id === deleteTarget.id) setSelectedReferenceData(null);
                setDeleteTarget(null);
            },
        });
    };

    const restoreReferenceData = (row: ReferenceDataRow) => {
        restoreForm.patch(route('hr.hr-reference-data.restore', row.id), {
            preserveScroll: true,
            onSuccess: () => {
                if (selectedReferenceData?.id === row.id) setSelectedReferenceData(null);
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
        const handleShortcut = (event: KeyboardEvent) => {
            const key = event.key.toLowerCase();
            const editable =
                event.target instanceof HTMLElement &&
                (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.target.isContentEditable);
            if ((event.metaKey || event.ctrlKey) && event.shiftKey && key === 'a' && canCreate) {
                event.preventDefault();
                startCreate();
                return;
            }
            if (((event.metaKey || event.ctrlKey) && key === 'k') || (event.key === '/' && !editable)) {
                event.preventDefault();
                document.getElementById('hr-reference-data-search-input')?.focus();
                return;
            }
            const focusTargets: Record<string, string> = {
                c: 'category-filter-trigger',
                s: 'status-filter-trigger',
                a: 'archive-filter-trigger',
                r: 'rows-filter-trigger',
                t: 'table-row-0',
            };
            if (event.altKey && focusTargets[key]) {
                event.preventDefault();
                document.getElementById(`hr-reference-data-${focusTargets[key]}`)?.focus();
                return;
            }
            if (event.altKey && key === 'p') {
                event.preventDefault();
                document
                    .querySelector<HTMLButtonElement>(
                        '#hr-reference-data-pagination-next:not(:disabled), #hr-reference-data-pagination-previous:not(:disabled)',
                    )
                    ?.focus();
                return;
            }
            if (event.key === 'Delete' && selectedReferenceData && canDelete && !selectedReferenceData.deleted_at) {
                event.preventDefault();
                setDeleteTarget(selectedReferenceData);
                return;
            }
            if (event.key === 'Escape') resetEditor();
        };
        window.addEventListener('keydown', handleShortcut);
        return () => window.removeEventListener('keydown', handleShortcut);
    }, [canCreate, canDelete, resetEditor, selectedReferenceData, startCreate]);

    return {
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
    };
}
