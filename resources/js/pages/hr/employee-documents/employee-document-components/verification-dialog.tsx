import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import type { EmployeeDocumentRow } from '../types';

export type VerificationAction = 'verify' | 'reject' | 'resubmit';

type Props = {
    document: EmployeeDocumentRow | null;
    action: VerificationAction | null;
    onClose: () => void;
};

const titles: Record<VerificationAction, string> = {
    verify: 'Verifikasi dokumen',
    reject: 'Tolak dokumen',
    resubmit: 'Kembalikan ke pending',
};

export function VerificationDialog({ document, action, onClose }: Props) {
    const form = useForm({ reason: '', status: '' });
    const close = () => {
        form.reset();
        form.clearErrors();
        onClose();
    };
    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (!document || !action) return;

        form.post(route(`hr.employee-documents.${action}`, document.id), {
            preserveScroll: true,
            onSuccess: close,
        });
    };

    return (
        <Dialog open={Boolean(document && action)} onOpenChange={(open) => !open && close()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{action ? titles[action] : 'Review dokumen'}</DialogTitle>
                    <DialogDescription>
                        {document?.document_type.name} milik {document?.employee.display_name}. Perubahan status dan actor akan dicatat dalam audit
                        log.
                    </DialogDescription>
                </DialogHeader>
                <form className="space-y-4" onSubmit={submit}>
                    {action !== 'resubmit' && (
                        <div className="space-y-2">
                            <Label htmlFor="employee-document-verification-reason">Catatan review {action === 'reject' ? '*' : '(opsional)'}</Label>
                            <Textarea
                                id="employee-document-verification-reason"
                                required={action === 'reject'}
                                maxLength={1000}
                                value={form.data.reason}
                                onChange={(event) => form.setData('reason', event.target.value)}
                            />
                            {form.errors.reason && <p className="text-destructive text-sm">{form.errors.reason}</p>}
                        </div>
                    )}
                    {form.errors.status && <p className="text-destructive text-sm">{form.errors.status}</p>}
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={close}>
                            Batal
                        </Button>
                        <Button type="submit" variant={action === 'reject' ? 'destructive' : 'default'} disabled={form.processing}>
                            {form.processing ? 'Memproses...' : 'Konfirmasi'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
