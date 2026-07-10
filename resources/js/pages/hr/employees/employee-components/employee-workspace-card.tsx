import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { EmployeeForm as EmployeeFormData, EmployeeOptions, EmployeeRow } from '../types';
import { EmployeeDetailCard } from './employee-detail-card';
import { EmployeeForm } from './employee-form';

export type EmployeeWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: EmployeeWorkspaceMode;
    employee: EmployeeRow | null;
    form: InertiaFormProps<EmployeeFormData>;
    options: EmployeeOptions;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function EmployeeWorkspaceCard({ mode, employee, form, options, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <EmployeeDetailCard employee={employee} />;
    }

    return (
        <Card data-dashboard-card>
            <CardHeader className="border-b">
                <CardTitle>{mode === 'create' ? 'Tambah Employee' : 'Edit Employee'}</CardTitle>
                <p className="text-sm text-muted-foreground">Kelola identitas inti, avatar, data kerja, dan relasi organisasi employee.</p>
            </CardHeader>
            <CardContent className="p-5">
                <EmployeeForm
                    form={form}
                    editing={mode === 'edit' ? employee : null}
                    options={options}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
