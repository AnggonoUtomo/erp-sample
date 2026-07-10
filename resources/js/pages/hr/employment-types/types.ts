import type { FormDataConvertible } from '@inertiajs/core';

export type EmploymentTypeRow = {
    id: number;
    code: string;
    name: string;
    description: string | null;
    requires_contract_end_date: boolean;
    included_in_payroll: boolean;
    eligible_for_benefits: boolean;
    eligible_for_overtime: boolean;
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

export type EmploymentTypeFilters = {
    search: string;
    status: string;
    archive: string;
    per_page: number;
};

export type EmploymentTypeSummary = {
    total: number;
    active: number;
    inactive: number;
    requires_contract_end_date: number;
    eligible_for_benefits: number;
    archived: number;
};

export interface EmploymentTypeForm extends Record<string, FormDataConvertible> {
    code: string;
    name: string;
    description: string;
    requires_contract_end_date: boolean;
    included_in_payroll: boolean;
    eligible_for_benefits: boolean;
    eligible_for_overtime: boolean;
    active: boolean;
}

export type EmploymentTypePageProps = {
    employmentTypes: Paginator<EmploymentTypeRow>;
    filters: EmploymentTypeFilters;
    summary: EmploymentTypeSummary;
};
