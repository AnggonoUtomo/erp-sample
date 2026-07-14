import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ClipboardCheck, Plus } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import { OnboardingArchiveDialog } from './onboarding-components/onboarding-archive-dialog';
import type { ContractOption, EmployeeOption, IdName, OnboardingDraftForm, OnboardingFilterForm, OnboardingPaginator, TemplateOption } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/dashboard' },
    { title: 'Employee Onboardings', href: '/hr/onboardings' },
];

type Props = {
    businessDate: string;
    onboardings: OnboardingPaginator;
    employeeOptions: EmployeeOption[];
    contractOptions: ContractOption[];
    templateOptions: TemplateOption[];
    ownerOptions: IdName[];
    filters: Partial<OnboardingFilterForm>;
};

export default function OnboardingsIndex({
    businessDate,
    onboardings,
    employeeOptions,
    contractOptions,
    templateOptions,
    ownerOptions,
    filters,
}: Props) {
    const { canAny } = usePermission();
    const canCreate = canAny(['onboardings.create', 'onboardings.manage']);
    const canArchive = canAny(['onboardings.archive', 'onboardings.manage']);
    const canRestore = canAny(['onboardings.restore', 'onboardings.manage']);
    const filterForm = useForm<OnboardingFilterForm>({
        employee_id: String(filters.employee_id ?? ''),
        owner_user_id: String(filters.owner_user_id ?? ''),
        template_id: String(filters.template_id ?? ''),
        status: String(filters.status ?? ''),
        start_from: String(filters.start_from ?? ''),
        start_to: String(filters.start_to ?? ''),
        overdue: Boolean(filters.overdue),
        archived: Boolean(filters.archived),
        business_date: businessDate,
    });
    const form = useForm<OnboardingDraftForm>({
        employee_id: '',
        employee_contract_id: '',
        onboarding_template_id: '',
        owner_user_id: '',
        start_date: '',
    });
    const employeeContracts = contractOptions.filter((contract) => String(contract.employee_id) === form.data.employee_id);

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.onboardings.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    };
    const applyFilters = (event: FormEvent) => {
        event.preventDefault();
        filterForm.get(route('hr.onboardings.index'), { preserveState: true, preserveScroll: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Employee Onboardings" />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                        <ClipboardCheck className="size-6" /> Employee Onboardings
                    </h1>
                    <p className="text-muted-foreground mt-1 text-sm">
                        Buat draft onboarding dari template. Checklist disalin sebagai snapshot dan tidak mengikuti perubahan template berikutnya.
                    </p>
                </div>

                <div className={`grid gap-6 ${canCreate ? 'xl:grid-cols-[minmax(0,1fr)_380px]' : ''}`}>
                    <div className="space-y-4">
                        <Card>
                            <CardContent className="p-4">
                                <form onSubmit={applyFilters} className="grid gap-3 md:grid-cols-3 xl:grid-cols-5">
                                    <FilterSelect
                                        value={filterForm.data.employee_id}
                                        onChange={(value) => filterForm.setData('employee_id', value)}
                                        placeholder="Semua employee"
                                        options={employeeOptions.map((item) => ({ value: String(item.id), label: item.display_name }))}
                                    />
                                    <FilterSelect
                                        value={filterForm.data.status}
                                        onChange={(value) => filterForm.setData('status', value)}
                                        placeholder="Semua status"
                                        options={['DRAFT', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'].map((value) => ({ value, label: value }))}
                                    />
                                    <FilterSelect
                                        value={filterForm.data.template_id}
                                        onChange={(value) => filterForm.setData('template_id', value)}
                                        placeholder="Semua template"
                                        options={templateOptions.map((item) => ({ value: String(item.id), label: item.name }))}
                                    />
                                    <FilterSelect
                                        value={filterForm.data.owner_user_id}
                                        onChange={(value) => filterForm.setData('owner_user_id', value)}
                                        placeholder="Semua owner"
                                        options={ownerOptions.map((item) => ({ value: String(item.id), label: item.name }))}
                                    />
                                    <Input
                                        type="date"
                                        aria-label="Business date"
                                        value={filterForm.data.business_date}
                                        onChange={(event) => filterForm.setData('business_date', event.target.value)}
                                    />
                                    <Input
                                        type="date"
                                        aria-label="Start date dari"
                                        value={filterForm.data.start_from}
                                        onChange={(event) => filterForm.setData('start_from', event.target.value)}
                                    />
                                    <Input
                                        type="date"
                                        aria-label="Start date sampai"
                                        value={filterForm.data.start_to}
                                        onChange={(event) => filterForm.setData('start_to', event.target.value)}
                                    />
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={filterForm.data.overdue}
                                            onChange={(event) => filterForm.setData('overdue', event.target.checked)}
                                        />{' '}
                                        Hanya overdue
                                    </label>
                                    <label className="flex items-center gap-2 text-sm">
                                        <input
                                            type="checkbox"
                                            checked={filterForm.data.archived}
                                            onChange={(event) => filterForm.setData('archived', event.target.checked)}
                                        />{' '}
                                        Histori arsip
                                    </label>
                                    <div className="flex gap-2">
                                        <Button type="submit" size="sm">
                                            Terapkan
                                        </Button>
                                        <Button type="button" size="sm" variant="outline" onClick={() => router.get(route('hr.onboardings.index'))}>
                                            Reset
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                        {onboardings.data.length === 0 && (
                            <Card>
                                <CardContent className="text-muted-foreground p-8 text-center">Belum ada onboarding.</CardContent>
                            </Card>
                        )}
                        {onboardings.data.map((onboarding) => (
                            <Card key={onboarding.id}>
                                <CardContent className="flex flex-wrap items-center justify-between gap-4 p-5">
                                    <div>
                                        <div className="font-medium">{onboarding.employee?.display_name ?? 'Employee tidak tersedia'}</div>
                                        <div className="text-muted-foreground mt-1 text-xs">
                                            {onboarding.employee?.employee_number} · {onboarding.template?.name} · Mulai {onboarding.start_date}
                                        </div>
                                        <div className="text-muted-foreground mt-1 text-xs">
                                            Owner: {onboarding.owner?.name ?? '—'} · {onboarding.tasks_count} task
                                        </div>
                                        <Button asChild variant="link" size="sm" className="mt-2 h-auto p-0">
                                            <Link href={route('hr.onboardings.show', { onboarding: onboarding.id, business_date: businessDate })}>
                                                Lihat detail
                                            </Link>
                                        </Button>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <Badge variant="secondary">{onboarding.status}</Badge>
                                        {((onboarding.archived && canRestore) ||
                                            (!onboarding.archived && canArchive && ['COMPLETED', 'CANCELLED'].includes(onboarding.status))) && (
                                            <OnboardingArchiveDialog onboardingId={onboarding.id} archived={onboarding.archived} />
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                        {onboardings.last_page > 1 && (
                            <div className="flex items-center justify-between">
                                <PageButton url={onboardings.prev_page_url}>Previous</PageButton>
                                <span className="text-muted-foreground text-xs">
                                    Halaman {onboardings.current_page} dari {onboardings.last_page}
                                </span>
                                <PageButton url={onboardings.next_page_url}>Next</PageButton>
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
                                                <SelectValue placeholder="Pilih employee" />
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
                                    <FormField label="Contract (opsional)" error={form.errors.employee_contract_id}>
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
                                    <FormField label="Template" error={form.errors.onboarding_template_id}>
                                        <Select
                                            value={form.data.onboarding_template_id}
                                            onValueChange={(value) => form.setData('onboarding_template_id', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Pilih template aktif" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {templateOptions.map((template) => (
                                                    <SelectItem key={template.id} value={String(template.id)}>
                                                        {template.code} — {template.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </FormField>
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
                                    <FormField label="Start date" error={form.errors.start_date}>
                                        <Input
                                            type="date"
                                            value={form.data.start_date}
                                            onChange={(event) => form.setData('start_date', event.target.value)}
                                        />
                                    </FormField>
                                    <Button className="w-full" type="submit" disabled={form.processing}>
                                        {form.processing ? 'Membuat…' : 'Buat draft onboarding'}
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

function FilterSelect({
    value,
    onChange,
    placeholder,
    options,
}: {
    value: string;
    onChange: (value: string) => void;
    placeholder: string;
    options: { value: string; label: string }[];
}) {
    return (
        <Select value={value || 'all'} onValueChange={(next) => onChange(next === 'all' ? '' : next)}>
            <SelectTrigger>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">{placeholder}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
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
