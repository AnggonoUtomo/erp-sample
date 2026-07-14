import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ClipboardCheck, Plus } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import type { ContractOption, EmployeeOption, IdName, OnboardingDraftForm, OnboardingPaginator, TemplateOption } from './types';

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
};

export default function OnboardingsIndex({ businessDate, onboardings, employeeOptions, contractOptions, templateOptions, ownerOptions }: Props) {
    const { canAny } = usePermission();
    const canCreate = canAny(['onboardings.create', 'onboardings.manage']);
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
                                    <Badge variant="secondary">{onboarding.status}</Badge>
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
