import type { FormDataConvertible } from '@inertiajs/core';

export type DepartementOption = {
    id: number;
    code: string;
    name: string;
};

export type PositionRow = {
    id: number;
    departement_id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    sort_order: number;
    departement: DepartementOption | null;
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

export type PositionFilters = {
    search: string;
    status: string;
    departement: string;
    per_page: number;
};

export type PositionSummary = {
    total: number;
    active: number;
    inactive: number;
    departements: number;
};

export interface PositionForm extends Record<string, FormDataConvertible> {
    departement_id: string;
    code: string;
    name: string;
    description: string;
    active: boolean;
}

export type PositionPageProps = {
    positions: Paginator<PositionRow>;
    departementOptions: DepartementOption[];
    filters: PositionFilters;
    summary: PositionSummary;
};
