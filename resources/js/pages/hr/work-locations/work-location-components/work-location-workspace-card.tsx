import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { MapPin, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { WorkLocationForm as WorkLocationFormData, WorkLocationPageProps, WorkLocationRow } from '../types';
import { WorkLocationForm } from './work-location-form';

export type WorkLocationWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: WorkLocationWorkspaceMode;
    workLocation: WorkLocationRow | null;
    form: InertiaFormProps<WorkLocationFormData>;
    canCreate: boolean;
    canUpdate: boolean;
    mapSettings: WorkLocationPageProps['mapSettings'];
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function WorkLocationWorkspaceCard({ mode, workLocation, form, canCreate, canUpdate, mapSettings, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return null;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : MapPin;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Work Location' : 'Tambah Work Location'}
                        </CardTitle>
                        <p className="text-muted-foreground text-sm">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <WorkLocationForm
                    form={form}
                    editing={isEdit ? workLocation : null}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    mapSettings={mapSettings}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
