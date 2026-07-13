import type { FormDataConvertible } from '@inertiajs/core';

export type RelatedLabel = {
    id: number;
    code: string;
    name: string;
};

export type EmployeeUser = {
    id: number;
    name: string;
    email: string;
};

export type EmployeeRow = {
    id: number;
    user_id: number | null;
    supervisor_id: number | null;
    departement_id: number | null;
    position_id: number | null;
    job_level_id: number | null;
    work_location_id: number | null;
    employment_status_id: number | null;
    employment_type_id: number | null;
    employee_number: string;
    first_name: string;
    last_name: string | null;
    display_name: string;
    work_email: string | null;
    personal_email: string | null;
    phone: string | null;
    date_of_birth: string | null;
    place_of_birth: string | null;
    national_id: string | null;
    address: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    emergency_contact_relation: string | null;
    hired_at: string | null;
    ended_at: string | null;
    notes: string | null;
    active: boolean;
    avatar: string | null;
    user: EmployeeUser | null;
    supervisor: { id: number; employee_number: string; display_name: string } | null;
    departement: RelatedLabel | null;
    position: RelatedLabel | null;
    job_level: RelatedLabel | null;
    work_location: RelatedLabel | null;
    employment_status: RelatedLabel | null;
    employment_type: RelatedLabel | null;
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

export type EmployeeFilters = {
    search: string;
    departement: string;
    status: string;
    archive: string;
    per_page: number;
};

export type EmployeeSummary = {
    total: number;
    active: number;
    inactive: number;
    linked_users: number;
    archived: number;
};

export type EmployeeOption = {
    value: number;
    label: string;
};

export type EmployeeOptions = {
    users: EmployeeOption[];
    supervisors: EmployeeOption[];
    departements: EmployeeOption[];
    positions: EmployeeOption[];
    jobLevels: EmployeeOption[];
    workLocations: EmployeeOption[];
    employmentStatuses: EmployeeOption[];
    employmentTypes: EmployeeOption[];
};

export interface EmployeeForm extends Record<string, FormDataConvertible> {
    user_id: string;
    supervisor_id: string;
    departement_id: string;
    position_id: string;
    job_level_id: string;
    work_location_id: string;
    employment_status_id: string;
    employment_type_id: string;
    employee_number: string;
    first_name: string;
    last_name: string;
    display_name: string;
    work_email: string;
    personal_email: string;
    phone: string;
    date_of_birth: string;
    place_of_birth: string;
    national_id: string;
    address: string;
    emergency_contact_name: string;
    emergency_contact_phone: string;
    emergency_contact_relation: string;
    hired_at: string;
    ended_at: string;
    notes: string;
    active: boolean;
    avatar: File | null;
    remove_avatar: boolean;
}

export type EmployeePageProps = {
    employees: Paginator<EmployeeRow>;
    options: EmployeeOptions;
    filters: EmployeeFilters;
    summary: EmployeeSummary;
};
