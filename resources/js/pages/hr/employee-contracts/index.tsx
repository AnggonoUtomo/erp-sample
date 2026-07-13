import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import { ArchiveContractDialog } from './employee-contract-components/archive-contract-dialog';
import { ContractLifecycleDialog, type LifecycleAction } from './employee-contract-components/contract-lifecycle-dialog';
import { SupersedeContractDialog } from './employee-contract-components/supersede-contract-dialog';
import type { ContractForm, ContractPageProps, ContractRow } from './types';

const emptyForm: ContractForm = {
    employee_id: '',
    employment_type_id: '',
    contract_number: '',
    start_date: '',
    end_date: '',
    probation_end_date: '',
    signed_date: '',
    notes: '',
};

export default function EmployeeContractsIndex({ contracts, options, filters }: ContractPageProps) {
    const { canAny } = usePermission();
    const canActivate = canAny(['employee-contracts.activate', 'employee-contracts.manage']);
    const canTerminate = canAny(['employee-contracts.terminate', 'employee-contracts.manage']);
    const canCancel = canAny(['employee-contracts.cancel', 'employee-contracts.manage']);
    const canSupersede = canAny(['employee-contracts.supersede', 'employee-contracts.manage']);
    const canArchive = canAny(['employee-contracts.delete', 'employee-contracts.manage']);
    const canRestore = canAny(['employee-contracts.restore', 'employee-contracts.manage']);
    const [lifecycleTarget, setLifecycleTarget] = useState<ContractRow | null>(null);
    const [lifecycleAction, setLifecycleAction] = useState<LifecycleAction | null>(null);
    const [supersedeTarget, setSupersedeTarget] = useState<ContractRow | null>(null);
    const [archiveTarget, setArchiveTarget] = useState<ContractRow | null>(null);
    const openLifecycle = (contract: ContractRow, action: LifecycleAction) => {
        setLifecycleTarget(contract);
        setLifecycleAction(action);
    };
    const form = useForm<ContractForm>(emptyForm);
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.employee-contracts.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'HR', href: '/hr/dashboard' },
                { title: 'Employee Contracts', href: '/hr/employee-contracts' },
            ]}
        >
            <Head title="Employee Contracts" />
            <div className="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader>
                        <div className="flex items-center justify-between gap-3">
                            <CardTitle>Employee Contracts</CardTitle>
                            <Select
                                value={filters.archive}
                                onValueChange={(archive) =>
                                    router.get(route('hr.employee-contracts.index'), { archive }, { preserveState: true, preserveScroll: true })
                                }
                            >
                                <SelectTrigger className="w-40" aria-label="Archive filter">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="active">Active records</SelectItem>
                                    <SelectItem value="with-trashed">All records</SelectItem>
                                    <SelectItem value="only-trashed">Archived records</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="overflow-x-auto rounded-md border">
                            <table className="w-full text-sm">
                                <thead className="bg-muted/50">
                                    <tr>
                                        <th className="p-3 text-left">Contract</th>
                                        <th className="p-3 text-left">Employee</th>
                                        <th className="p-3 text-left">Period</th>
                                        <th className="p-3 text-left">Status / Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {contracts.data.map((contract) => (
                                        <tr key={contract.id} className="border-t">
                                            <td className="p-3 font-medium">{contract.contract_number}</td>
                                            <td className="p-3">
                                                {contract.employee.display_name}
                                                <span className="text-muted-foreground block text-xs">{contract.employment_type.name}</span>
                                            </td>
                                            <td className="p-3">
                                                {contract.start_date} — {contract.end_date ?? 'Open ended'}
                                            </td>
                                            <td className="p-3">
                                                <span>{contract.archived ? 'ARCHIVED' : contract.status}</span>
                                                {!contract.archived && contract.status === 'DRAFT' && canActivate && (
                                                    <Button
                                                        className="ml-2"
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() =>
                                                            router.post(
                                                                route('hr.employee-contracts.activate', contract.id),
                                                                {},
                                                                { preserveScroll: true },
                                                            )
                                                        }
                                                    >
                                                        Activate
                                                    </Button>
                                                )}
                                                {!contract.archived && contract.status === 'ACTIVE' && canTerminate && (
                                                    <Button
                                                        className="ml-2"
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() => openLifecycle(contract, 'terminate')}
                                                    >
                                                        Terminate
                                                    </Button>
                                                )}
                                                {!contract.archived && contract.status === 'ACTIVE' && canSupersede && (
                                                    <Button className="ml-2" size="sm" variant="outline" onClick={() => setSupersedeTarget(contract)}>
                                                        Supersede
                                                    </Button>
                                                )}
                                                {!contract.archived && ['DRAFT', 'ACTIVE'].includes(contract.status) && canCancel && (
                                                    <Button
                                                        className="ml-2"
                                                        size="sm"
                                                        variant="destructive"
                                                        onClick={() => openLifecycle(contract, 'cancel')}
                                                    >
                                                        Cancel
                                                    </Button>
                                                )}
                                                {!contract.archived && canArchive && (
                                                    <Button
                                                        className="ml-2"
                                                        size="sm"
                                                        variant="destructive"
                                                        onClick={() => setArchiveTarget(contract)}
                                                    >
                                                        Archive
                                                    </Button>
                                                )}
                                                {contract.archived && canRestore && (
                                                    <Button className="ml-2" size="sm" variant="outline" onClick={() => setArchiveTarget(contract)}>
                                                        Restore
                                                    </Button>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        {contracts.data.length === 0 && (
                            <p className="text-muted-foreground py-8 text-center text-sm">Belum ada employee contract.</p>
                        )}
                        <p className="text-muted-foreground mt-3 text-xs">Total {contracts.total} contract</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Create Draft Contract</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-4" onSubmit={submit}>
                            <Select value={form.data.employee_id} onValueChange={(value) => form.setData('employee_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih employee" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.employees.map((item) => (
                                        <SelectItem key={item.value} value={String(item.value)}>
                                            {item.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.employee_id && <p className="text-destructive text-sm">{form.errors.employee_id}</p>}
                            <Select value={form.data.employment_type_id} onValueChange={(value) => form.setData('employment_type_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih employment type" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.employmentTypes.map((item) => (
                                        <SelectItem key={item.value} value={String(item.value)}>
                                            {item.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Input
                                placeholder="Contract number"
                                value={form.data.contract_number}
                                onChange={(event) => form.setData('contract_number', event.target.value)}
                            />
                            {form.errors.contract_number && <p className="text-destructive text-sm">{form.errors.contract_number}</p>}
                            <div className="grid grid-cols-2 gap-3">
                                <Input
                                    type="date"
                                    value={form.data.start_date}
                                    onChange={(event) => form.setData('start_date', event.target.value)}
                                />
                                <Input type="date" value={form.data.end_date} onChange={(event) => form.setData('end_date', event.target.value)} />
                            </div>
                            {(form.errors.start_date || form.errors.end_date) && (
                                <p className="text-destructive text-sm">{form.errors.start_date ?? form.errors.end_date}</p>
                            )}
                            <textarea
                                className="border-input w-full rounded-md border px-3 py-2 text-sm"
                                rows={3}
                                placeholder="Internal notes"
                                value={form.data.notes}
                                onChange={(event) => form.setData('notes', event.target.value)}
                            />
                            <Button className="w-full" disabled={form.processing}>
                                Create Draft
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
            <ContractLifecycleDialog
                contract={lifecycleTarget}
                action={lifecycleAction}
                onClose={() => {
                    setLifecycleTarget(null);
                    setLifecycleAction(null);
                }}
            />
            <SupersedeContractDialog contract={supersedeTarget} employmentTypes={options.employmentTypes} onClose={() => setSupersedeTarget(null)} />
            <ArchiveContractDialog contract={archiveTarget} onClose={() => setArchiveTarget(null)} />
        </AppLayout>
    );
}
