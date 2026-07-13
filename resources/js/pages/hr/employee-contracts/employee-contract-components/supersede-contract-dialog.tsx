import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { ContractRow, Option } from '../types';

type Props = { contract: ContractRow | null; employmentTypes: Option[]; onClose: () => void };

function nextDate(date: string): string {
    const value = new Date(`${date}T00:00:00Z`);
    value.setUTCDate(value.getUTCDate() + 1);

    return value.toISOString().slice(0, 10);
}

export function SupersedeContractDialog({ contract, employmentTypes, onClose }: Props) {
    const form = useForm({
        employment_type_id: '',
        contract_number: '',
        start_date: '',
        end_date: '',
        probation_end_date: '',
        signed_date: '',
        reason: '',
        notes: '',
        status: '',
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (!contract) return;
        form.post(route('hr.employee-contracts.supersede', contract.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };
    const minimumStartDate = contract ? nextDate(contract.start_date) : undefined;

    return (
        <Dialog open={Boolean(contract)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent className="sm:max-w-xl">
                <DialogHeader>
                    <DialogTitle>Supersede Contract</DialogTitle>
                    <DialogDescription>
                        Create an active replacement for {contract?.contract_number}. Employee remains {contract?.employee.display_name}.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    <div className="grid gap-3 sm:grid-cols-2">
                        <Select value={form.data.employment_type_id} onValueChange={(value) => form.setData('employment_type_id', value)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Employment type" />
                            </SelectTrigger>
                            <SelectContent>
                                {employmentTypes.map((item) => (
                                    <SelectItem key={item.value} value={String(item.value)}>
                                        {item.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Input
                            aria-label="Replacement contract number"
                            placeholder="Contract number"
                            value={form.data.contract_number}
                            onChange={(event) => form.setData('contract_number', event.target.value)}
                        />
                        <Input
                            aria-label="Replacement start date"
                            type="date"
                            min={minimumStartDate}
                            value={form.data.start_date}
                            onChange={(event) => form.setData('start_date', event.target.value)}
                        />
                        <Input
                            aria-label="Replacement end date"
                            type="date"
                            min={form.data.start_date || undefined}
                            value={form.data.end_date}
                            onChange={(event) => form.setData('end_date', event.target.value)}
                        />
                    </div>
                    {Object.entries(form.errors).map(([field, message]) => (
                        <p key={field} className="text-destructive text-sm">
                            {message}
                        </p>
                    ))}
                    <div className="space-y-2">
                        <label htmlFor="supersede-reason" className="text-sm font-medium">
                            Reason
                        </label>
                        <textarea
                            id="supersede-reason"
                            required
                            maxLength={500}
                            rows={2}
                            className="border-input w-full rounded-md border px-3 py-2 text-sm"
                            value={form.data.reason}
                            onChange={(event) => form.setData('reason', event.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <label htmlFor="supersede-notes" className="text-sm font-medium">
                            Replacement notes
                        </label>
                        <textarea
                            id="supersede-notes"
                            maxLength={2000}
                            rows={2}
                            className="border-input w-full rounded-md border px-3 py-2 text-sm"
                            value={form.data.notes}
                            onChange={(event) => form.setData('notes', event.target.value)}
                        />
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Back
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? 'Processing...' : 'Create replacement'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
