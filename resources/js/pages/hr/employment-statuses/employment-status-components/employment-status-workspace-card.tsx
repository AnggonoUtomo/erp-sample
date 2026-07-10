import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { BadgeCheck, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { EmploymentStatusForm as EmploymentStatusFormData, EmploymentStatusRow } from '../types';
import { EmploymentStatusDetailCard } from './employment-status-detail-card';
import { EmploymentStatusForm } from './employment-status-form';

export type EmploymentStatusWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: EmploymentStatusWorkspaceMode;
    employmentStatus: EmploymentStatusRow | null;
    form: InertiaFormProps<EmploymentStatusFormData>;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function EmploymentStatusWorkspaceCard({ mode, employmentStatus, form, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <EmploymentStatusDetailCard employmentStatus={employmentStatus} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : BadgeCheck;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Employment Status' : 'Tambah Employment Status'}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <EmploymentStatusForm
                    form={form}
                    editing={isEdit ? employmentStatus : null}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
