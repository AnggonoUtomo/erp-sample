export type HRReportOption = {
    value: number;
    label: string;
};

export type HeadcountReportRow = {
    id: number | null;
    code: string | null;
    name: string;
    employeeCount: number;
};

export type HeadcountReport = {
    asOf: string;
    groupBy: 'departement' | 'work_location' | 'employment_status';
    rows: HeadcountReportRow[];
    total: number;
};

export type ExpiryReportRow = {
    employeeId: number;
    employeeNumber: string;
    employeeName: string;
    typeLabel: string;
    expiresAt: string;
    daysRemaining: number;
    state: 'EXPIRED' | 'EXPIRING';
};

export type ExpiryReport = {
    asOf: string;
    withinDays: number;
    rows: ExpiryReportRow[];
    total: number;
};

export type HRReportsPageProps = {
    meta: {
        status: 'read-only-boundary';
        scope: string[];
    };
    filters: {
        as_of: string;
        employment_status_id: number | null;
        contract_within_days: number;
        document_within_days: number;
    };
    options: {
        employmentStatuses: HRReportOption[];
    };
    headcount: {
        byDepartement: HeadcountReport;
        byWorkLocation: HeadcountReport;
        byEmploymentStatus: HeadcountReport;
    };
    contractExpiry: ExpiryReport;
    documentExpiry: ExpiryReport;
};
