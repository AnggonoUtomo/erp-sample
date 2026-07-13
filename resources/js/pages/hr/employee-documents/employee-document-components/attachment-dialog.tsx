import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router, useForm } from '@inertiajs/react';
import { Download, Link2Off, Upload } from 'lucide-react';
import { useEffect, useState } from 'react';
import type { EmployeeDocumentRow } from '../types';

type AttachmentForm = { attachment: File | null; idempotency_key: string };
type DeliveryResponse = { data?: { token?: string }; error?: { message?: string } };

export function AttachmentDialog({ document, onClose }: { document: EmployeeDocumentRow | null; onClose: () => void }) {
    const form = useForm<AttachmentForm>({ attachment: null, idempotency_key: '' });

    useEffect(() => {
        if (document && document.attachment_state !== 'ATTACHED' && form.data.idempotency_key === '') {
            form.setData('idempotency_key', crypto.randomUUID());
        }
    }, [document, form]);

    const attach = () => {
        if (!document) return;
        form.post(route('hr.employee-documents.attachment.store', document.id), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                onClose();
            },
        });
    };
    const detach = () => {
        if (!document) return;
        router.delete(route('hr.employee-documents.attachment.destroy', document.id), { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <Dialog open={Boolean(document)} onOpenChange={(open) => !open && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{document?.attachment_state === 'ATTACHED' ? 'Kelola file dokumen' : 'Upload file dokumen'}</DialogTitle>
                    <DialogDescription>
                        File disimpan pada private Document Management. HR hanya menyimpan reference dan tidak dapat melihat path penyimpanan.
                    </DialogDescription>
                </DialogHeader>
                {document?.attachment_state === 'ATTACHED' ? (
                    <p className="text-muted-foreground text-sm">Lepas reference jika file salah. File DMS tidak akan dihapus.</p>
                ) : (
                    <div className="space-y-2">
                        <Label htmlFor="employee-document-attachment">PDF, JPG, JPEG, atau PNG — maksimum 20 MB</Label>
                        <Input
                            id="employee-document-attachment"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                            onChange={(event) => form.setData('attachment', event.target.files?.[0] ?? null)}
                        />
                        {form.errors.attachment && <p className="text-destructive text-xs">{form.errors.attachment}</p>}
                        {form.errors.idempotency_key && <p className="text-destructive text-xs">{form.errors.idempotency_key}</p>}
                    </div>
                )}
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Batal
                    </Button>
                    {document?.attachment_state === 'PENDING' && (
                        <Button variant="destructive" onClick={detach}>
                            <Link2Off className="size-4" /> Batalkan pending
                        </Button>
                    )}
                    {document?.attachment_state === 'ATTACHED' ? (
                        <Button variant="destructive" onClick={detach}>
                            <Link2Off className="size-4" /> Lepas reference
                        </Button>
                    ) : (
                        <Button disabled={!form.data.attachment || form.processing} onClick={attach}>
                            <Upload className="size-4" /> Upload
                        </Button>
                    )}
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

export function DownloadAttachmentButton({ document }: { document: EmployeeDocumentRow }) {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const download = async () => {
        setLoading(true);
        setError(null);
        try {
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), Accept: 'application/json' };
            const issue = await fetch(route('hr.employee-documents.attachment.delivery', document.id), {
                method: 'POST',
                headers,
                credentials: 'same-origin',
                body: '{}',
            });
            const handoff = (await issue.json()) as DeliveryResponse;
            if (!issue.ok || !handoff.data?.token) throw new Error(handoff.error?.message ?? 'Akses file ditolak.');

            const response = await fetch(route('document-management.deliveries.consume'), {
                method: 'POST',
                headers,
                credentials: 'same-origin',
                body: JSON.stringify({ token: handoff.data.token }),
            });
            if (!response.ok) throw new Error('Token file kedaluwarsa atau akses telah dicabut. Silakan coba lagi.');
            const blob = await response.blob();
            const objectUrl = URL.createObjectURL(blob);
            const anchor = window.document.createElement('a');
            anchor.href = objectUrl;
            anchor.download = responseFilename(response.headers.get('content-disposition'));
            anchor.click();
            URL.revokeObjectURL(objectUrl);
        } catch (exception) {
            setError(exception instanceof Error ? exception.message : 'File tidak dapat diunduh.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="mt-2">
            <Button size="sm" variant="outline" disabled={loading} onClick={download}>
                <Download className="size-4" /> {loading ? 'Menyiapkan...' : 'Download'}
            </Button>
            {error && <p className="text-destructive mt-1 max-w-48 text-xs">{error}</p>}
        </div>
    );
}

function csrfToken(): string {
    const token = window.document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
    if (!token) throw new Error('Session keamanan tidak tersedia. Muat ulang halaman.');
    return token;
}

function responseFilename(disposition: string | null): string {
    const encoded = disposition?.match(/filename\*=UTF-8''([^;]+)/i)?.[1];
    const plain = disposition?.match(/filename="([^"]+)"/i)?.[1];
    const candidate = encoded ? decodeURIComponent(encoded) : plain;
    return (
        candidate
            ?.split('')
            .map((character) => (character.charCodeAt(0) < 32 || '\\/:*?"<>|'.includes(character) ? '_' : character))
            .join('') || 'employee-document'
    );
}
