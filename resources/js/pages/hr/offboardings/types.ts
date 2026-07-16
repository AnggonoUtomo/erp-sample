import type { FormDataConvertible } from '@inertiajs/core';

export type IdName = { id: number; name: string };
export type EmployeeOption = { id: number; employee_number: string; display_name: string };
export type ContractOption = { id: number; employee_id: number; contract_number: string; start_date: string };
export type CodeName = { id: number; code: string; name: string };
export type OffboardingRow = {
    id: number;
    employee: EmployeeOption | null;
    template: CodeName | null;
    target_status: CodeName | null;
    owner: IdName | null;
    exit_date: string;
    exit_type: string;
    status: string;
    tasks_count: number;
};
export type OffboardingPaginator = {
    data: OffboardingRow[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
};
export interface OffboardingDraftForm extends Record<string, FormDataConvertible> {
    employee_id: string;
    employee_contract_id: string;
    offboarding_template_id: string;
    target_employment_status_id: string;
    owner_user_id: string;
    exit_date: string;
    exit_type: string;
    exit_reason: string;
    notes: string;
}
