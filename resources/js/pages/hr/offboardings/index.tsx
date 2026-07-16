import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { LogOut, Plus } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import type { CodeName, ContractOption, EmployeeOption, IdName, OffboardingDraftForm, OffboardingPaginator } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/dashboard' },
    { title: 'Employee Offboardings', href: '/hr/offboardings' },
];

const exitTypes = [
    { value: 'RESIGNATION', label: 'Pengunduran diri' },
    { value: 'TERMINATION', label: 'Pemutusan hubungan kerja' },
    { value: 'END_OF_CONTRACT', label: 'Kontrak berakhir' },
    { value: 'RETIREMENT', label: 'Pensiun' },
    { value: 'OTHER', label: 'Lainnya' },
];

type Props = {
    offboardings: OffboardingPaginator;
    employeeOptions: EmployeeOption[];
    contractOptions: ContractOption[];
    templateOptions: CodeName[];
    targetStatusOptions: CodeName[];
    ownerOptions: IdName[];
};

export default function OffboardingsIndex({
    offboardings,
    employeeOptions,
    contractOptions,
    templateOptions,
    targetStatusOptions,
    ownerOptions,
}: Props) {
    const { canAny } = usePermission();
    const canCreate = canAny(['offboardings.create', 'offboardings.manage']);
    const form = useForm<OffboardingDraftForm>({
        employee_id: '',
        employee_contract_id: '',
        offboarding_template_id: '',
        target_employment_status_id: '',
        owner_user_id: '',
        exit_date: '',
        exit_type: '',
        exit_reason: '',
        notes: '',
    });
    const employeeContracts = contractOptions.filter((contract) => String(contract.employee_id) === form.data.employee_id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.offboardings.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employee Offboardings" />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                        <LogOut className="size-6" /> Employee Offboardings
                    </h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Siapkan proses keluar sebagai draft. Checklist menjadi snapshot dan belum mengubah profil employee atau contract.
                    </p>
                </div>

                <div className={`grid gap-6 ${canCreate ? 'xl:grid-cols-[minmax(0,1fr)_400px]' : ''}`}>
                    <div className="space-y-4">
                        {offboardings.data.length === 0 && (
                            <Card>
                                <CardContent className="text-muted-foreground p-8 text-center">Belum ada draft offboarding.</CardContent>
                            </Card>
                        )}
                        {offboardings.data.map((offboarding) => (
                            <Card key={offboarding.id}>
                                <CardContent className="flex flex-wrap items-center justify-between gap-4 p-5">
                                    <div>
                                        <div className="font-medium">{offboarding.employee?.display_name ?? 'Employee tidak tersedia'}</div>
                                        <div className="text-muted-foreground mt-1 text-xs">
                                            {offboarding.employee?.employee_number} · Exit {offboarding.exit_date} · {offboarding.template?.name}
                                        </div>
                                        <div className="text-muted-foreground mt-1 text-xs">
                                            Target: {offboarding.target_status?.name ?? '—'} · Owner: {offboarding.owner?.name ?? '—'} ·{' '}
                                            {offboarding.tasks_count} task
                                        </div>
                                    </div>
                                    <Badge variant="secondary">{offboarding.status}</Badge>
                                </CardContent>
                            </Card>
                        ))}
                        {offboardings.last_page > 1 && (
                            <div className="flex items-center justify-between">
                                <PageButton url={offboardings.prev_page_url}>Previous</PageButton>
                                <span className="text-muted-foreground text-xs">
                                    Halaman {offboardings.current_page} dari {offboardings.last_page}
                                </span>
                                <PageButton url={offboardings.next_page_url}>Next</PageButton>
                            </div>
                        )}
                    </div>

                    {canCreate && (
                        <Card className="h-fit xl:sticky xl:top-6">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <Plus className="size-4" /> Draft baru
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-4">
                                    <FormField label="Employee" error={form.errors.employee_id}>
                                        <Select
                                            value={form.data.employee_id}
                                            onValueChange={(value) => {
                                                form.setData('employee_id', value);
                                                form.setData('employee_contract_id', '');
                                            }}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih employee aktif" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {employeeOptions.map((employee) => (
                                                    <SelectItem key={employee.id} value={String(employee.id)}>
                                                        {employee.employee_number} — {employee.display_name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                    <FormField label="Contract aktif (opsional)" error={form.errors.employee_contract_id}>
                                        <Select
                                            value={form.data.employee_contract_id || 'none'}
                                            onValueChange={(value) => form.setData('employee_contract_id', value === 'none' ? '' : value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Tanpa contract" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="none">Tanpa contract</SelectItem>
                                                {employeeContracts.map((contract) => (
                                                    <SelectItem key={contract.id} value={String(contract.id)}>
                                                        {contract.contract_number} · {contract.start_date}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                    <FormSelect
                                        label="Template"
                                        error={form.errors.offboarding_template_id}
                                        value={form.data.offboarding_template_id}
                                        onChange={(value) => form.setData('offboarding_template_id', value)}
                                        options={templateOptions}
                                        placeholder="Pilih template aktif"
                                    />
                                    <FormSelect
                                        label="Status akhir"
                                        error={form.errors.target_employment_status_id}
                                        value={form.data.target_employment_status_id}
                                        onChange={(value) => form.setData('target_employment_status_id', value)}
                                        options={targetStatusOptions}
                                        placeholder="Pilih status final"
                                    />
                                    <FormField label="Owner" error={form.errors.owner_user_id}>
                                        <Select value={form.data.owner_user_id} onValueChange={(value) => form.setData('owner_user_id', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih owner" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {ownerOptions.map((owner) => (
                                                    <SelectItem key={owner.id} value={String(owner.id)}>
                                                        {owner.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                    <FormField label="Tanggal keluar" error={form.errors.exit_date}>
                                        <Input
                                            type="date"
                                            value={form.data.exit_date}
                                            onChange={(event) => form.setData('exit_date', event.target.value)}
                                        />
                                    </FormField>
                                    <FormField label="Jenis keluar" error={form.errors.exit_type}>
                                        <Select value={form.data.exit_type} onValueChange={(value) => form.setData('exit_type', value)}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih jenis keluar" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {exitTypes.map((type) => (
                                                    <SelectItem key={type.value} value={type.value}>
                                                        {type.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
                                    <FormField label="Alasan" error={form.errors.exit_reason}>
                                        <Textarea
                                            value={form.data.exit_reason}
                                            onChange={(event) => form.setData('exit_reason', event.target.value)}
                                            rows={3}
                                            maxLength={2000}
                                        />
                                    </FormField>
                                    <FormField label="Catatan (opsional)" error={form.errors.notes}>
                                        <Textarea
                                            value={form.data.notes}
                                            onChange={(event) => form.setData('notes', event.target.value)}
                                            rows={2}
                                            maxLength={4000}
                                        />
                                    </FormField>
                                    <Button className="w-full" type="submit" disabled={form.processing}>
                                        {form.processing ? 'Membuat…' : 'Buat draft offboarding'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

function FormSelect({
    label,
    error,
    value,
    onChange,
    options,
    placeholder,
}: {
    label: string;
    error?: string;
    value: string;
    onChange: (value: string) => void;
    options: CodeName[];
    placeholder: string;
}) {
    return (
        <FormField label={label} error={error}>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger>
                    <SelectValue placeholder={placeholder} />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.id} value={String(option.id)}>
                            {option.code} — {option.name}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </FormField>
    );
}

function FormField({ label, error, children }: { label: string; error?: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            {children}
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}

function PageButton({ url, children }: { url: string | null; children: ReactNode }) {
    return (
        <Button asChild={Boolean(url)} variant="outline" size="sm" disabled={!url}>
            {url ? (
                <Link href={url} preserveScroll>
                    {children}
                </Link>
            ) : (
                <span>{children}</span>
            )}
        </Button>
    );
}
