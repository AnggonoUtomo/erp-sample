import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { ImageIcon, Palette, Save, Send, Upload, X } from 'lucide-react';
import type { FormEvent } from 'react';
import type { BrandingForm } from '../types';

type Props = {
    can: { update: boolean };
    form: InertiaFormProps<BrandingForm>;
    logoPreview: string | null;
    faviconPreview: string | null;
    submit: (event: FormEvent) => void;
};

export function BrandingSettingsPanel({ can, form, logoPreview, faviconPreview, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-rose flex size-10 items-center justify-center rounded-md">
                        <Palette className="size-5" />
                    </span>
                    Branding Aplikasi
                </CardTitle>
                <CardDescription>Atur nama aplikasi, logo sidebar, dan favicon browser.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="space-y-2">
                        <FieldInfoLabel
                            htmlFor="app_name"
                            required
                            description="Nama ini dipakai pada title halaman dan identitas aplikasi di layout."
                        >
                            Nama Aplikasi
                        </FieldInfoLabel>
                        <Input
                            id="app_name"
                            value={form.data.app_name}
                            disabled={!can.update || form.processing}
                            placeholder="Laravel Starter Kit"
                            onChange={(event) => form.setData('app_name', event.target.value)}
                        />
                        <InputError message={form.errors.app_name} />
                    </div>

                    <div className="grid gap-4 lg:grid-cols-2">
                        <ImageUploadCard
                            title="Logo Aplikasi"
                            description="Logo utama untuk area sidebar/header. Format PNG, JPG, WEBP, atau SVG."
                            hint="Rekomendasi rasio kotak atau horizontal ringkas."
                            preview={logoPreview}
                            accept=".png,.jpg,.jpeg,.webp,.svg"
                            disabled={!can.update || form.processing}
                            emptyIcon={<ImageIcon className="text-muted-foreground size-8" />}
                            imageClassName="max-h-14 max-w-14 object-contain"
                            onRemove={() => {
                                form.setData('logo', null);
                                form.setData('remove_logo', true);
                            }}
                            onChange={(file) => {
                                form.setData('logo', file);
                                form.setData('remove_logo', false);
                            }}
                            error={form.errors.logo}
                        />
                        <ImageUploadCard
                            title="Favicon"
                            description="Ikon kecil yang muncul di tab browser. Format ICO, PNG, JPG, WEBP, atau SVG."
                            hint="Rekomendasi 32x32 atau 64x64 pixel."
                            preview={faviconPreview}
                            accept=".ico,.png,.jpg,.jpeg,.webp,.svg"
                            disabled={!can.update || form.processing}
                            emptyIcon={<ImageIcon className="text-muted-foreground size-8" />}
                            imageClassName="max-h-12 max-w-12 object-contain"
                            onRemove={() => {
                                form.setData('favicon', null);
                                form.setData('remove_favicon', true);
                            }}
                            onChange={(file) => {
                                form.setData('favicon', file);
                                form.setData('remove_favicon', false);
                            }}
                            error={form.errors.favicon}
                        />
                    </div>

                    <div className="rounded-lg border border-dashed p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-sky flex size-9 shrink-0 items-center justify-center rounded-md">
                                <Upload className="size-4" />
                            </span>
                            <div>
                                <p className="text-sm font-medium">Preview Runtime</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                                    Setelah disimpan, nama aplikasi langsung dipakai sebagai title halaman dan logo akan muncul pada identitas layout.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={!can.update || form.processing} className="h-11 min-w-40">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Branding
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

type ImageUploadCardProps = {
    title: string;
    description: string;
    hint: string;
    preview: string | null;
    accept: string;
    disabled: boolean;
    emptyIcon: React.ReactNode;
    imageClassName: string;
    onRemove: () => void;
    onChange: (file: File | null) => void;
    error?: string;
};

function ImageUploadCard({
    title,
    description,
    hint,
    preview,
    accept,
    disabled,
    emptyIcon,
    imageClassName,
    onRemove,
    onChange,
    error,
}: ImageUploadCardProps) {
    return (
        <div className="bg-background/60 space-y-4 rounded-lg border p-4">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <FieldInfoLabel description={description}>{title}</FieldInfoLabel>
                    <p className="text-muted-foreground mt-1 text-xs">{hint}</p>
                </div>
                {preview && (
                    <Button type="button" variant="ghost" size="icon" disabled={disabled} onClick={onRemove}>
                        <X className="size-4" />
                    </Button>
                )}
            </div>
            <div className="flex items-center gap-4">
                <div className="bg-muted flex size-20 shrink-0 items-center justify-center rounded-lg border">
                    {preview ? <img src={preview} alt={title} className={imageClassName} /> : emptyIcon}
                </div>
                <div className="min-w-0 flex-1 space-y-2">
                    <Input type="file" accept={accept} disabled={disabled} onChange={(event) => onChange(event.target.files?.[0] ?? null)} />
                    <InputError message={error} />
                </div>
            </div>
        </div>
    );
}
