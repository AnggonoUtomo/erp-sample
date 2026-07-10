export type QueueOverview = {
    connection: string;
    jobs_table: string;
    failed_table: string;
    pending: number | null;
    reserved: number | null;
    failed: number | null;
};

export type PendingJob = {
    id: number;
    queue: string;
    name: string;
    attempts: number;
    reserved_at: string | null;
    available_at: string | null;
    created_at: string | null;
};

export type FailedJob = {
    id: number;
    uuid: string;
    connection: string;
    queue: string;
    name: string;
    exception: string;
    failed_at: string;
};

export type Paginator<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
