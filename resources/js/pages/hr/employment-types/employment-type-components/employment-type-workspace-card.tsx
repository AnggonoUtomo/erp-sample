import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { BriefcaseBusiness, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { EmploymentTypeForm as EmploymentTypeFormData, EmploymentTypeRow } from '../types';
import { EmploymentTypeDetailCard } from './employment-type-detail-card';
import { EmploymentTypeForm } from './employment-type-form';

export type EmploymentTypeWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: EmploymentTypeWorkspaceMode;
    employmentType: EmploymentTypeRow | null;
    form: InertiaFormProps<EmploymentTypeFormData>;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function EmploymentTypeWorkspaceCard({ mode, employmentType, form, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <EmploymentTypeDetailCard employmentType={employmentType} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : BriefcaseBusiness;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Employment Type' : 'Tambah Employment Type'}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <EmploymentTypeForm
                    form={form}
                    editing={isEdit ? employmentType : null}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
