import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { ContractRow } from '../types';

export type LifecycleAction = 'terminate' | 'cancel';

type Props = {
    contract: ContractRow | null;
    action: LifecycleAction | null;
    onClose: () => void;
};

export function ContractLifecycleDialog({ contract, action, onClose }: Props) {
    const form = useForm({ end_date: contract?.end_date ?? '', reason: '', status: '' });
    const isTerminate = action === 'terminate';

    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (!contract || !action) return;

        form.post(route(`hr.employee-contracts.${action}`, contract.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };

    return (
        <Dialog open={Boolean(contract && action)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{isTerminate ? 'Terminate Contract' : 'Cancel Contract'}</DialogTitle>
                    <DialogDescription>
                        {contract?.contract_number} — aksi lifecycle ini tersimpan dalam audit log dan tidak dapat dibatalkan lewat edit biasa.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {isTerminate && (
                        <div className="space-y-2">
                            <label htmlFor="contract-end-date" className="text-sm font-medium">
                                Effective end date
                            </label>
                            <Input
                                id="contract-end-date"
                                type="date"
                                min={contract?.start_date}
                                max={contract?.end_date ?? undefined}
                                value={form.data.end_date}
                                onChange={(event) => form.setData('end_date', event.target.value)}
                            />
                            {form.errors.end_date && <p className="text-destructive text-sm">{form.errors.end_date}</p>}
                        </div>
                    )}
                    <div className="space-y-2">
                        <label htmlFor="contract-lifecycle-reason" className="text-sm font-medium">
                            Reason
                        </label>
                        <textarea
                            id="contract-lifecycle-reason"
                            required
                            maxLength={500}
                            rows={3}
                            className="border-input w-full rounded-md border px-3 py-2 text-sm"
                            value={form.data.reason}
                            onChange={(event) => form.setData('reason', event.target.value)}
                        />
                        {form.errors.reason && <p className="text-destructive text-sm">{form.errors.reason}</p>}
                        {form.errors.status && <p className="text-destructive text-sm">{form.errors.status}</p>}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={onClose}>
                            Back
                        </Button>
                        <Button type="submit" variant={isTerminate ? 'default' : 'destructive'} disabled={form.processing}>
                            {form.processing ? 'Processing...' : isTerminate ? 'Terminate' : 'Cancel contract'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
