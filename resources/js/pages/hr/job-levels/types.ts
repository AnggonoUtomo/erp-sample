import type { FormDataConvertible } from '@inertiajs/core';

export type JobLevelRow = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    active: boolean;
    sort_order: number;
    deleted_at: string | null;
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

export type JobLevelFilters = {
    search: string;
    status: string;
    archive: string;
    per_page: number;
};

export type JobLevelSummary = {
    total: number;
    active: number;
    inactive: number;
    archived: number;
};

export interface JobLevelForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    description: string;
    active: boolean;
}

export type JobLevelPageProps = {
    jobLevels: Paginator<JobLevelRow>;
    filters: JobLevelFilters;
    summary: JobLevelSummary;
};
