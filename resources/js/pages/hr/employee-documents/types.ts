import type { FormDataConvertible } from '@inertiajs/core';

export type Option = { value: number; label: string };
export type DocumentTypeOption = Option & { requires_expiry: boolean; requires_number: boolean };
export type EmployeeDocumentRow = {
    id: number;
    employee: { id: number; employee_number: string; display_name: string };
    document_type: { id: number; code: string; name: string };
    document_number_masked: string | null;
    issuer: string | null;
    issued_at: string | null;
    expires_at: string | null;
    verification_status: 'PENDING' | 'VERIFIED' | 'REJECTED';
    notes: string | null;
    created_at: string;
};
export type DocumentForm = Record<string, FormDataConvertible> & {
    employee_id: string;
    document_type_id: string;
    document_number: string;
    issuer: string;
    issued_at: string;
    expires_at: string;
    notes: string;
};
export type EmployeeDocumentPageProps = {
    documents: {
        data: EmployeeDocumentRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    options: { employees: Option[]; documentTypes: DocumentTypeOption[] };
    filters: { employee: number | ''; document_type: number | ''; status: string };
};
