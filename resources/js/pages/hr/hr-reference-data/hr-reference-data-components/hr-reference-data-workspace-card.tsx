import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { ListFilter, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { ReferenceDataForm as ReferenceDataFormData, ReferenceDataOption, ReferenceDataRow } from '../types';
import { HRReferenceDataDetailCard } from './hr-reference-data-detail-card';
import { HRReferenceDataForm } from './hr-reference-data-form';

export type HRReferenceDataWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: HRReferenceDataWorkspaceMode;
    referenceData: ReferenceDataRow | null;
    categoryOptions: ReferenceDataOption[];
    form: InertiaFormProps<ReferenceDataFormData>;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function HRReferenceDataWorkspaceCard({ mode, referenceData, form, categoryOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <HRReferenceDataDetailCard referenceData={referenceData} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : ListFilter;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Reference Data' : 'Tambah Reference Data'}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <HRReferenceDataForm
                    form={form}
                    editing={isEdit ? referenceData : null}
                    categoryOptions={categoryOptions}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
