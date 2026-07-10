import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { Building2, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { DepartementForm as DepartementFormData, DepartementOption, DepartementRow } from '../types';
import { DepartementDetailCard } from './departement-detail-card';
import { DepartementForm } from './departement-form';

export type DepartementWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: DepartementWorkspaceMode;
    departement: DepartementRow | null;
    form: InertiaFormProps<DepartementFormData>;
    parentOptions: DepartementOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function DepartementWorkspaceCard({ mode, departement, form, parentOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <DepartementDetailCard departement={departement} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : Building2;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-emerald flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Departement' : 'Tambah Departement'}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <DepartementForm
                    form={form}
                    editing={isEdit ? departement : null}
                    parentOptions={parentOptions}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
