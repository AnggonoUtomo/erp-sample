import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import type { EmployeeDocumentRow } from '../types';

export function ArchiveDocumentDialog({ document, onClose }: { document: EmployeeDocumentRow | null; onClose: () => void }) {
    const submit = () => {
        if (!document) return;
        const options = { preserveScroll: true, onSuccess: onClose };
        if (document.archived) {
            router.patch(route('hr.employee-documents.restore', document.id), {}, options);
        } else {
            router.delete(route('hr.employee-documents.destroy', document.id), options);
        }
    };

    return (
        <Dialog open={Boolean(document)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{document?.archived ? 'Pulihkan metadata' : 'Arsipkan metadata'}</DialogTitle>
                    <DialogDescription>
                        {document?.archived
                            ? 'Restore akan memeriksa ulang employee, tipe dokumen, dan duplicate number.'
                            : 'Metadata tetap disimpan sebagai histori. Aksi ini tidak menghapus file atau data Document Management.'}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Batal
                    </Button>
                    <Button variant={document?.archived ? 'default' : 'destructive'} onClick={submit}>
                        {document?.archived ? 'Pulihkan' : 'Arsipkan'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
