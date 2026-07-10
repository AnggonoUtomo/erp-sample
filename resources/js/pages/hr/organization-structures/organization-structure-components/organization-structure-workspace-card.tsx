import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import type { InertiaFormProps } from '@inertiajs/react';
import { Network, Pencil } from 'lucide-react';
import type { FormEvent } from 'react';
import type { OrganizationStructureForm as FormData, OrganizationStructureRow, SimpleOption } from '../types';
import { OrganizationStructureDetailCard } from './organization-structure-detail-card';
import { OrganizationStructureForm } from './organization-structure-form';

export type OrganizationStructureWorkspaceMode = 'detail' | 'create' | 'edit';
type Props = { mode: OrganizationStructureWorkspaceMode; organizationStructure: OrganizationStructureRow | null; form: InertiaFormProps<FormData>; parentOptions: SimpleOption[]; departementOptions: SimpleOption[]; positionOptions: SimpleOption[]; nodeTypeOptions: SimpleOption[]; canCreate: boolean; canUpdate: boolean; onSubmit: (event: FormEvent) => void; onCancel: () => void };

export function OrganizationStructureWorkspaceCard(props: Props) {
    if (props.mode === 'detail') return <OrganizationStructureDetailCard organizationStructure={props.organizationStructure} />;
    const isEdit = props.mode === 'edit';
    const Icon = isEdit ? Pencil : Network;
    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="space-y-4 border-b"><CardTitle className="flex items-center gap-2 text-lg"><span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg"><Icon className="size-4" /></span>{isEdit ? 'Edit Organization Structure' : 'Tambah Organization Structure'}</CardTitle><p className="text-sm text-muted-foreground">Field bertanda bintang wajib diisi. Relasi parent dipakai untuk membentuk hierarchy.</p></CardHeader>
            <CardContent className="p-5"><OrganizationStructureForm form={props.form} editing={isEdit ? props.organizationStructure : null} parentOptions={props.parentOptions} departementOptions={props.departementOptions} positionOptions={props.positionOptions} nodeTypeOptions={props.nodeTypeOptions} canCreate={props.canCreate} canUpdate={props.canUpdate} onSubmit={props.onSubmit} onCancel={props.onCancel} /></CardContent>
        </Card>
    );
}
