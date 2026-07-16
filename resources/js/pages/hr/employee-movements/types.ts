export type Option = { value: number; label: string };
export type PositionOption = Option & { departement_id: number };
export type SnapshotValue = { id: number | null; label: string };
export type MovementSnapshot = {
    departement_id: SnapshotValue;
    position_id: SnapshotValue;
    job_level_id: SnapshotValue;
    employment_status_id: SnapshotValue;
    employment_type_id: SnapshotValue;
    work_location_id: SnapshotValue;
    supervisor_id: SnapshotValue;
};
export type MovementRow = {
    id: number;
    type: 'TRANSFER' | 'PROMOTION' | 'DEMOTION' | 'EMPLOYMENT_CHANGE';
    effective_date: string;
    status: 'DRAFT' | 'APPROVED' | 'APPLIED' | 'CANCELLED';
    reason: string;
    notes: string | null;
    employee: { id: number; display_name: string; employee_number: string };
    before: MovementSnapshot;
    after: MovementSnapshot;
    creator: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    applier: { id: number; name: string } | null;
    approved_at: string | null;
    applied_at: string | null;
    cancelled_at: string | null;
    cancel_reason: string | null;
};
export type MovementPageProps = {
    movements: { data: MovementRow[] };
    options: {
        today: string;
        employees: Option[];
        departments: Option[];
        positions: PositionOption[];
        jobLevels: Option[];
        employmentStatuses: Option[];
        employmentTypes: Option[];
        locations: Option[];
        supervisors: Option[];
    };
};
export type MovementForm = {
    employee_id: string;
    type: string;
    effective_date: string;
    departement_id: string;
    position_id: string;
    job_level_id: string;
    employment_status_id: string;
    employment_type_id: string;
    work_location_id: string;
    supervisor_id: string;
    reason: string;
    notes: string;
    movement: string;
};
