import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { Layers, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { JobLevelForm as JobLevelFormData, JobLevelRow } from '../types';
import { JobLevelDetailCard } from './job-level-detail-card';
import { JobLevelForm } from './job-level-form';

export type JobLevelWorkspaceMode = 'detail' | 'create' | 'edit';

type Props = {
    mode: JobLevelWorkspaceMode;
    jobLevel: JobLevelRow | null;
    form: InertiaFormProps<JobLevelFormData>;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function JobLevelWorkspaceCard({ mode, jobLevel, form, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    if (mode === 'detail') {
        return <JobLevelDetailCard jobLevel={jobLevel} />;
    }

    const isEdit = mode === 'edit';
    const Icon = isEdit ? Pencil : Layers;

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div className="space-y-1">
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <Icon className="size-4" />
                            </span>
                            {isEdit ? 'Edit Job Level' : 'Tambah Job Level'}
                        </CardTitle>
                        <p className="text-sm text-muted-foreground">
                            Field bertanda bintang wajib diisi. Gunakan tooltip tanda tanya untuk memahami fungsi setiap field.
                        </p>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="p-5">
                <JobLevelForm
                    form={form}
                    editing={isEdit ? jobLevel : null}
                    canCreate={canCreate}
                    canUpdate={canUpdate}
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                />
            </CardContent>
        </Card>
    );
}
