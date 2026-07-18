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

export type HRReportsPageProps = {
    meta: {
        status: 'read-only-boundary';
        scope: string[];
    };
    filters: {
        as_of: string;
        employment_status_id: number | null;
    };
    options: {
        employmentStatuses: HRReportOption[];
    };
    headcount: {
        byDepartement: HeadcountReport;
        byWorkLocation: HeadcountReport;
        byEmploymentStatus: HeadcountReport;
    };
};
