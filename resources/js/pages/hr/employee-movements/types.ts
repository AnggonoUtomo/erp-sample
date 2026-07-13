export type Option = { value: number; label: string };
export type PositionOption = Option & { departement_id: number };
export type SnapshotValue = { id: number | null; label: string };
export type MovementSnapshot = {
    departement_id: SnapshotValue;
    position_id: SnapshotValue;
    work_location_id: SnapshotValue;
    supervisor_id: SnapshotValue;
};
export type MovementRow = {
    id: number;
    type: 'TRANSFER';
    effective_date: string;
    status: 'DRAFT' | 'APPLIED';
    reason: string;
    notes: string | null;
    employee: { id: number; display_name: string; employee_number: string };
    before: MovementSnapshot;
    after: MovementSnapshot;
    creator: { id: number; name: string } | null;
    applier: { id: number; name: string } | null;
    applied_at: string | null;
};
export type MovementPageProps = {
    movements: { data: MovementRow[] };
    options: {
        today: string;
        employees: Option[];
        departments: Option[];
        positions: PositionOption[];
        locations: Option[];
        supervisors: Option[];
    };
};
export type MovementForm = {
    employee_id: string;
    effective_date: string;
    departement_id: string;
    position_id: string;
    work_location_id: string;
    supervisor_id: string;
    reason: string;
    notes: string;
    movement: string;
};
