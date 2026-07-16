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
export type OffboardingProgress = {
    total: number;
    terminal: number;
    completed: number;
    skipped: number;
    required_incomplete: number;
    optional_incomplete: number;
    overdue: number;
    percentage: number;
};
export type OffboardingTaskRow = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    required: boolean;
    due_offset_days: number;
    due_date: string;
    default_assignee_role: string | null;
    sort_order: number;
    status: string;
    overdue: boolean;
    assignee: IdName | null;
    completed_by: IdName | null;
    completed_at: string | null;
    completion_note: string | null;
    skipped_by: IdName | null;
    skipped_at: string | null;
    skip_reason: string | null;
    reopened_by: IdName | null;
    reopened_at: string | null;
    reopen_reason: string | null;
};
export type OffboardingDetail = {
    id: number;
    employee: EmployeeOption | null;
    contract: { id: number; contract_number: string; start_date: string; end_date: string | null; status: string } | null;
    template: CodeName | null;
    target_status: CodeName | null;
    owner: IdName | null;
    exit_date: string;
    exit_type: string;
    exit_reason: string;
    notes: string | null;
    status: string;
    archived: boolean;
    created_at: string;
    cancelled_by: IdName | null;
    cancelled_at: string | null;
    cancel_reason: string | null;
    progress: OffboardingProgress;
    tasks: OffboardingTaskRow[];
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
