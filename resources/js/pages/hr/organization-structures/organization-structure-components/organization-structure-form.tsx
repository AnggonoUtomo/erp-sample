import { FieldInfoLabel } from '@/components/field-info-label';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent } from 'react';
import type { OrganizationStructureForm as FormData, OrganizationStructureRow, SimpleOption } from '../types';

type Props = {
    form: InertiaFormProps<FormData>;
    editing: OrganizationStructureRow | null;
    parentOptions: SimpleOption[];
    departementOptions: SimpleOption[];
    positionOptions: SimpleOption[];
    nodeTypeOptions: SimpleOption[];
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

export function OrganizationStructureForm({
    form,
    editing,
    parentOptions,
    departementOptions,
    positionOptions,
    nodeTypeOptions,
    canCreate,
    canUpdate,
    onSubmit,
    onCancel,
}: Props) {
    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="code" required description="Kode unik node struktur organisasi. Contoh: COMPANY, ORG-HRD, TEAM-PAYROLL.">
                        Code
                    </FieldInfoLabel>
                    <Input id="code" value={form.data.code} onChange={(event) => form.setData('code', event.target.value)} placeholder="ORG-HRD" />
                    {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="node_type"
                        required
                        description="Jenis node untuk membedakan company, division, departement, unit, team, atau position."
                    >
                        Node Type
                    </FieldInfoLabel>
                    <Select value={form.data.node_type} onValueChange={(value) => form.setData('node_type', value)}>
                        <SelectTrigger id="node_type">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {nodeTypeOptions.map((option) => (
                                <SelectItem key={String(option.value)} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {form.errors.node_type && <p className="text-destructive text-sm">{form.errors.node_type}</p>}
                </div>
            </div>
            <div className="space-y-2">
                <FieldInfoLabel htmlFor="name" required description="Nama node yang tampil di struktur organisasi dan approval.">
                    Nama Structure
                </FieldInfoLabel>
                <Input
                    id="name"
                    value={form.data.name}
                    onChange={(event) => form.setData('name', event.target.value)}
                    placeholder="Human Resources"
                />
                {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
            </div>
            <div className="space-y-2">
                <FieldInfoLabel htmlFor="parent_id" description="Parent menentukan hierarchy. Kosongkan untuk root node seperti Company.">
                    Parent Structure
                </FieldInfoLabel>
                <Select value={form.data.parent_id || 'none'} onValueChange={(value) => form.setData('parent_id', value === 'none' ? '' : value)}>
                    <SelectTrigger id="parent_id">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Tanpa parent</SelectItem>
                        {parentOptions
                            .filter((option) => Number(option.value) !== editing?.id)
                            .map((option) => (
                                <SelectItem key={String(option.value)} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                    </SelectContent>
                </Select>
                {form.errors.parent_id && <p className="text-destructive text-sm">{form.errors.parent_id}</p>}
            </div>
            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel
                        htmlFor="departement_id"
                        description="Departement terkait untuk node ini. Kosongkan jika node hanya grup/holding."
                    >
                        Departement
                    </FieldInfoLabel>
                    <Select
                        value={form.data.departement_id || 'none'}
                        onValueChange={(value) => form.setData('departement_id', value === 'none' ? '' : value)}
                    >
                        <SelectTrigger id="departement_id">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">Tanpa departement</SelectItem>
                            {departementOptions.map((option) => (
                                <SelectItem key={String(option.value)} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="position_id" description="Position terkait jika node ini mewakili jabatan tertentu dalam hierarchy.">
                        Position
                    </FieldInfoLabel>
                    <Select
                        value={form.data.position_id || 'none'}
                        onValueChange={(value) => form.setData('position_id', value === 'none' ? '' : value)}
                    >
                        <SelectTrigger id="position_id">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="none">Tanpa position</SelectItem>
                            {positionOptions.map((option) => (
                                <SelectItem key={String(option.value)} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>
            <div className="space-y-2">
                <FieldInfoLabel htmlFor="description" description="Keterangan fungsi node dalam organization chart, reporting line, atau approval.">
                    Deskripsi
                </FieldInfoLabel>
                <textarea
                    id="description"
                    value={form.data.description}
                    onChange={(event) => form.setData('description', event.target.value)}
                    rows={4}
                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-24 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                />
            </div>
            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Structure aktif</span>
                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                        Structure aktif bisa dipakai untuk assignment, approval, dan reporting.
                    </span>
                </span>
            </label>
            <div className="flex gap-2">
                {(canCreate || canUpdate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Structure'}
                    </Button>
                )}
                {editing && (
                    <Button type="button" variant="outline" onClick={onCancel}>
                        Batal
                    </Button>
                )}
            </div>
        </form>
    );
}
