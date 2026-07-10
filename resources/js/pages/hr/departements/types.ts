import type { FormDataConvertible } from '@inertiajs/core';

export type DepartementOption = {
    id: number;
    code: string;
    name: string;
};

export type DepartementRow = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    sort_order: number;
    children_count: number;
    parent: DepartementOption | null;
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

export type DepartementFilters = {
    search: string;
    status: string;
    per_page: number;
};

export type DepartementSummary = {
    total: number;
    active: number;
    inactive: number;
    root: number;
};

export interface DepartementForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    parent_id: string;
    description: string;
    active: boolean;
}

export type DepartementPageProps = {
    departements: Paginator<DepartementRow>;
    departementOptions: DepartementOption[];
    filters: DepartementFilters;
    summary: DepartementSummary;
};
