import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { AlertTriangle, LoaderCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Toaster, toast } from 'sonner';

type InertiaErrors = Record<string, string | string[]>;

function humanizeField(field: string) {
    return field.replaceAll('_', ' ').replaceAll('.', ' ');
}

function errorMessageFrom(errors: InertiaErrors | undefined) {
    if (!errors || Object.keys(errors).length === 0) {
        return 'Periksa kembali input, permission, atau kondisi session yang digunakan.';
    }

    return Object.entries(errors)
        .map(([field, message]) => {
            const text = Array.isArray(message) ? message.join(' ') : message;

            return `${humanizeField(field)}: ${text}`;
        })
        .join('\n');
}

export function AppNotifications() {
    const [loading, setLoading] = useState(false);
    const [errorDialog, setErrorDialog] = useState<{ title: string; message: string } | null>(null);
    const lastFlash = useRef<string | null>(null);

    useEffect(() => {
        const removeStartListener = router.on('start', () => setLoading(true));
        const removeFinishListener = router.on('finish', () => setLoading(false));
        const removeErrorListener = router.on('error', (event) => {
            setErrorDialog({
                title: 'Request gagal diproses',
                message: errorMessageFrom(event.detail.errors as InertiaErrors | undefined),
            });
        });
        const removeSuccessListener = router.on('success', (event) => {
            const flash = event.detail.page.props.flash as { success?: string | null; error?: string | null } | undefined;
            const message = flash?.success ?? flash?.error ?? null;

            if (!message || lastFlash.current === message) {
                return;
            }

            lastFlash.current = message;

            if (flash?.success) {
                toast.success(flash.success);
                return;
            }

            if (flash?.error) {
                setErrorDialog({
                    title: 'Aksi tidak dapat dilanjutkan',
                    message: flash.error,
                });
            }
        });

        return () => {
            removeStartListener();
            removeFinishListener();
            removeErrorListener();
            removeSuccessListener();
        };
    }, []);

    return (
        <>
            <Toaster richColors position="top-right" closeButton />
            <Dialog open={Boolean(errorDialog)} onOpenChange={(open) => !open && setErrorDialog(null)}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <div className="bg-destructive/15 text-destructive mb-2 flex size-11 items-center justify-center rounded-lg">
                            <AlertTriangle className="size-5" />
                        </div>
                        <DialogTitle>{errorDialog?.title ?? 'Terjadi kendala'}</DialogTitle>
                        <DialogDescription className="leading-relaxed">{errorDialog?.message}</DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button type="button" onClick={() => setErrorDialog(null)}>
                            Mengerti
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            {loading ? (
                <div className="bg-background/95 text-primary fixed right-5 bottom-5 z-50 flex size-11 items-center justify-center rounded-full border shadow-lg backdrop-blur">
                    <LoaderCircle className="size-5 animate-spin" />
                </div>
            ) : null}
        </>
    );
}
