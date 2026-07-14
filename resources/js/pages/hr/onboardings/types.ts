import type { FormDataConvertible } from '@inertiajs/core';

export type IdName = { id: number; name: string };
export type EmployeeOption = { id: number; employee_number: string; display_name: string };
export type ContractOption = { id: number; employee_id: number; contract_number: string; start_date: string };
export type TemplateOption = { id: number; code: string; name: string };
export type OnboardingRow = {
    id: number;
    employee: { id: number; employee_number: string; display_name: string } | null;
    template: TemplateOption | null;
    owner: IdName | null;
    start_date: string;
    status: string;
    archived: boolean;
    tasks_count: number;
};
export interface OnboardingFilterForm extends Record<string, FormDataConvertible> {
    employee_id: string;
    owner_user_id: string;
    template_id: string;
    status: string;
    start_from: string;
    start_to: string;
    overdue: boolean;
    archived: boolean;
    business_date: string;
}
export type OnboardingPaginator = {
    data: OnboardingRow[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    total: number;
};
export type OnboardingProgress = {
    total: number;
    terminal: number;
    completed: number;
    required_incomplete: number;
    optional_incomplete: number;
    overdue: number;
    percentage: number;
};
export type OnboardingTaskRow = {
    id: number;
    title: string;
    description: string | null;
    category: string;
    required: boolean;
    due_date: string;
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
export type OnboardingDetail = {
    id: number;
    employee: EmployeeOption | null;
    contract: { id: number; contract_number: string; start_date: string; end_date: string | null; status: string } | null;
    template: TemplateOption | null;
    owner: IdName | null;
    start_date: string;
    status: string;
    archived: boolean;
    created_at: string;
    completed_by: IdName | null;
    completed_at: string | null;
    cancelled_by: IdName | null;
    cancelled_at: string | null;
    cancel_reason: string | null;
    progress: OnboardingProgress;
    tasks: OnboardingTaskRow[];
};
export interface OnboardingDraftForm extends Record<string, FormDataConvertible> {
    employee_id: string;
    employee_contract_id: string;
    onboarding_template_id: string;
    owner_user_id: string;
    start_date: string;
}
