import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { BriefcaseBusiness, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { DepartementOption, PositionForm, PositionRow } from '../types';
import { PositionDetailCard } from './position-detail-card';
import { PositionForm as PositionFormComponent } from './position-form';

export type PositionWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: PositionWorkspaceMode;
    position: PositionRow | null;
    form: InertiaFormProps<PositionForm>;
    departementOptions: DepartementOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function PositionWorkspaceCard({ mode, position, form, departementOptions, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <PositionDetailCard position={position} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : BriefcaseBusiness;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="border-b bg-muted/20">
                <CardTitle className="flex items-center gap-2 text-base">
                    <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-lg">
                        <Icon className="size-4" />
                    </span>
                    {isEdit ? 'Edit Position' : 'Tambah Position'}
                </CardTitle>
            </CardHeader>
            <CardContent className="p-5">
                <PositionFormComponent
                    form={form}
                    editing={isEdit ? position : null}
                    departementOptions={departementOptions}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
