import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { ArrowRight, ListRestart } from 'lucide-react';
import type { FormEvent } from 'react';
import type { MovementForm, MovementPageProps, MovementSnapshot } from './types';

const fields: Array<[keyof MovementSnapshot, string]> = [
    ['departement_id', 'Department'],
    ['position_id', 'Position'],
    ['job_level_id', 'Job level'],
    ['employment_status_id', 'Employment status'],
    ['employment_type_id', 'Employment type'],
    ['work_location_id', 'Work location'],
    ['supervisor_id', 'Supervisor'],
];

const movementTypes = [
    { value: 'TRANSFER', label: 'Transfer' },
    { value: 'PROMOTION', label: 'Promotion' },
    { value: 'DEMOTION', label: 'Demotion' },
    { value: 'EMPLOYMENT_CHANGE', label: 'Employment change' },
];

export default function EmployeeMovementsIndex({ movements, options }: MovementPageProps) {
    const { canAny } = usePermission();
    const canCreate = canAny(['employee-movements.create', 'employee-movements.manage']);
    const canApply = canAny(['employee-movements.apply', 'employee-movements.manage']);
    const form = useForm<MovementForm>({
        employee_id: '',
        type: 'TRANSFER',
        effective_date: options.today,
        departement_id: '',
        position_id: '',
        job_level_id: '',
        employment_status_id: '',
        employment_type_id: '',
        work_location_id: '',
        supervisor_id: '',
        reason: '',
        notes: '',
        movement: '',
    });
    const positions = form.data.departement_id
        ? options.positions.filter((position) => position.departement_id === Number(form.data.departement_id))
        : options.positions;
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.employee-movements.store'), {
            preserveScroll: true,
            onSuccess: () =>
                form.reset(
                    'employee_id',
                    'type',
                    'departement_id',
                    'position_id',
                    'job_level_id',
                    'employment_status_id',
                    'employment_type_id',
                    'work_location_id',
                    'supervisor_id',
                    'reason',
                    'notes',
                ),
        });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'HR', href: '/hr/dashboard' },
                { title: 'Employee Movements', href: '/hr/employee-movements' },
            ]}
        >
            <Head title="Employee Movements" />
            <div className="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <ListRestart className="size-5" /> Histori movement
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {movements.data.length === 0 && <p className="text-muted-foreground text-sm">Belum ada movement.</p>}
                        {movements.data.map((movement) => (
                            <article key={movement.id} className="rounded-lg border p-4">
                                <div className="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p className="font-medium">{movement.employee.display_name}</p>
                                        <p className="text-muted-foreground text-sm">
                                            {movement.employee.employee_number} · {movement.type} · efektif {movement.effective_date}
                                        </p>
                                    </div>
                                    <span className="bg-muted rounded-full px-2.5 py-1 text-xs font-medium">{movement.status}</span>
                                </div>
                                <div className="mt-4 grid gap-2 text-sm">
                                    {fields.map(([key, label]) => (
                                        <div
                                            key={key}
                                            className="bg-muted/40 grid items-center gap-2 rounded-md p-2 sm:grid-cols-[120px_1fr_auto_1fr]"
                                        >
                                            <span className="text-muted-foreground">{label}</span>
                                            <span>{movement.before[key].label}</span>
                                            <ArrowRight className="text-muted-foreground size-4" />
                                            <span className={movement.before[key].id !== movement.after[key].id ? 'text-primary font-semibold' : ''}>
                                                {movement.after[key].label}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-3 flex flex-wrap items-center justify-between gap-3">
                                    <p className="text-sm">
                                        <span className="text-muted-foreground">Alasan:</span> {movement.reason}
                                    </p>
                                    {canApply && movement.status === 'DRAFT' && (
                                        <Button
                                            size="sm"
                                            onClick={() =>
                                                router.post(route('hr.employee-movements.apply', movement.id), {}, { preserveScroll: true })
                                            }
                                        >
                                            Terapkan hari ini
                                        </Button>
                                    )}
                                </div>
                            </article>
                        ))}
                    </CardContent>
                </Card>

                {canCreate && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Buat draft movement</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form className="space-y-4" onSubmit={submit}>
                                <SelectField
                                    label="Jenis movement"
                                    value={form.data.type}
                                    options={movementTypes}
                                    onChange={(value) => form.setData('type', value)}
                                    error={form.errors.type}
                                />
                                <SelectField
                                    label="Employee"
                                    value={form.data.employee_id}
                                    options={options.employees}
                                    onChange={(value) => form.setData('employee_id', value)}
                                    error={form.errors.employee_id}
                                />
                                <div className="space-y-2">
                                    <Label htmlFor="effective-date">Effective date</Label>
                                    <Input id="effective-date" type="date" value={form.data.effective_date} readOnly />
                                    {form.errors.effective_date && <p className="text-destructive text-xs">{form.errors.effective_date}</p>}
                                </div>
                                <SelectField
                                    label="Department tujuan"
                                    value={form.data.departement_id}
                                    options={options.departments}
                                    onChange={(value) => {
                                        form.setData('departement_id', value);
                                        form.setData('position_id', '');
                                    }}
                                    error={form.errors.departement_id}
                                />
                                <SelectField
                                    label="Position tujuan"
                                    value={form.data.position_id}
                                    options={positions}
                                    onChange={(value) => form.setData('position_id', value)}
                                    error={form.errors.position_id}
                                />
                                <SelectField
                                    label="Job level tujuan"
                                    value={form.data.job_level_id}
                                    options={options.jobLevels}
                                    onChange={(value) => form.setData('job_level_id', value)}
                                    error={form.errors.job_level_id}
                                />
                                <SelectField
                                    label="Employment status tujuan"
                                    value={form.data.employment_status_id}
                                    options={options.employmentStatuses}
                                    onChange={(value) => form.setData('employment_status_id', value)}
                                    error={form.errors.employment_status_id}
                                />
                                <SelectField
                                    label="Employment type tujuan"
                                    value={form.data.employment_type_id}
                                    options={options.employmentTypes}
                                    onChange={(value) => form.setData('employment_type_id', value)}
                                    error={form.errors.employment_type_id}
                                />
                                <SelectField
                                    label="Work location tujuan"
                                    value={form.data.work_location_id}
                                    options={options.locations}
                                    onChange={(value) => form.setData('work_location_id', value)}
                                    error={form.errors.work_location_id}
                                />
                                <SelectField
                                    label="Supervisor tujuan"
                                    value={form.data.supervisor_id}
                                    options={options.supervisors.filter((item) => item.value !== Number(form.data.employee_id))}
                                    onChange={(value) => form.setData('supervisor_id', value)}
                                    error={form.errors.supervisor_id}
                                />
                                <div className="space-y-2">
                                    <Label htmlFor="movement-reason">Alasan</Label>
                                    <Input
                                        id="movement-reason"
                                        value={form.data.reason}
                                        onChange={(event) => form.setData('reason', event.target.value)}
                                    />
                                    {(form.errors.reason || form.errors.movement) && (
                                        <p className="text-destructive text-xs">{form.errors.reason || form.errors.movement}</p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="movement-notes">Catatan (opsional)</Label>
                                    <textarea
                                        id="movement-notes"
                                        className="bg-background min-h-20 w-full rounded-md border px-3 py-2 text-sm"
                                        value={form.data.notes}
                                        onChange={(event) => form.setData('notes', event.target.value)}
                                    />
                                </div>
                                <Button className="w-full" disabled={form.processing}>
                                    Simpan DRAFT
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

function SelectField({
    label,
    value,
    options,
    onChange,
    error,
}: {
    label: string;
    value: string;
    options: Array<{ value: number | string; label: string }>;
    onChange: (value: string) => void;
    error?: string;
}) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger>
                    <SelectValue placeholder="Pilih..." />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={String(option.value)}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}
