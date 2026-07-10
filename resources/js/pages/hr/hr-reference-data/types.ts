import type { FormDataConvertible } from '@inertiajs/core';

export type ReferenceDataRow = {
    id: number;
    category: string;
    code: string;
    name: string;
    description: string | null;
    metadata: Record<string, unknown> | null;
    active: boolean;
    sort_order: number;
    deleted_at: string | null;
    created_at: string | null;
    updated_at: string | null;
};

export type ReferenceCategoryRow = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    sort_order: number;
    items_count: number;
    created_at: string | null;
    updated_at: string | null;
};

export type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

export type ReferenceDataFilters = {
    search: string;
    category: string;
    status: string;
    archive: string;
    per_page: number;
};

export type ReferenceDataSummary = {
    total: number;
    active: number;
    inactive: number;
    categories: number;
    archived: number;
};

export type ReferenceDataOption = {
    value: string;
    label: string;
};

export interface ReferenceDataForm extends Record<string, FormDataConvertible> {
    category: string;
    code: string;
    name: string;
    description: string;
    active: boolean;
}

export interface ReferenceCategoryForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    description: string;
    active: boolean;
}

export type ReferenceDataPageProps = {
    referenceData: Paginator<ReferenceDataRow>;
    categoryOptions: ReferenceDataOption[];
    referenceCategories: ReferenceCategoryRow[];
    filters: ReferenceDataFilters;
    summary: ReferenceDataSummary;
};
