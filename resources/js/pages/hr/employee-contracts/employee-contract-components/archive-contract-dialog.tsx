import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import type { ContractRow } from '../types';

type Props = { contract: ContractRow | null; onClose: () => void };

export function ArchiveContractDialog({ contract, onClose }: Props) {
    const restore = contract?.archived ?? false;
    const submit = () => {
        if (!contract) return;

        const options = { preserveScroll: true, onSuccess: onClose };
        if (restore) {
            router.patch(route('hr.employee-contracts.restore', contract.id), {}, options);
        } else {
            router.delete(route('hr.employee-contracts.destroy', contract.id), options);
        }
    };

    return (
        <Dialog open={Boolean(contract)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{restore ? 'Restore Contract' : 'Archive Contract'}</DialogTitle>
                    <DialogDescription>
                        {restore
                            ? `Restore ${contract?.contract_number} to the active contract list? Restore is rejected if its period overlaps.`
                            : `Archive ${contract?.contract_number}? Its history remains available and no data is permanently deleted.`}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" onClick={onClose}>
                        Back
                    </Button>
                    <Button type="button" variant={restore ? 'default' : 'destructive'} onClick={submit}>
                        {restore ? 'Restore' : 'Archive'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
