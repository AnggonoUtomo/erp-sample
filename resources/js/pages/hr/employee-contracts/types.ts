import type { FormDataConvertible } from '@inertiajs/core';

export type Option = { value: number; label: string };
export type ContractRow = {
    id: number;
    employee_id: number;
    employment_type_id: number;
    contract_number: string;
    start_date: string;
    end_date: string | null;
    status: 'DRAFT' | 'ACTIVE' | 'ENDED' | 'CANCELLED';
    ended_reason: string | null;
    superseded_by_id: number | null;
    archived: boolean;
    notes: string | null;
    employee: { id: number; display_name: string };
    employment_type: { id: number; name: string };
};
export type ContractForm = Record<string, FormDataConvertible> & {
    employee_id: string;
    employment_type_id: string;
    contract_number: string;
    start_date: string;
    end_date: string;
    probation_end_date: string;
    signed_date: string;
    notes: string;
};
export type ContractPageProps = {
    contracts: { data: ContractRow[]; current_page: number; last_page: number; total: number };
    options: { employees: Option[]; employmentTypes: Option[] };
    filters: { archive: 'active' | 'with-trashed' | 'only-trashed' };
};
