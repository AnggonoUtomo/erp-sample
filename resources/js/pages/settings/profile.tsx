import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ImagePlus, Trash2 } from 'lucide-react';
import { type FormEventHandler, useEffect, useRef, useState } from 'react';

import DeleteUser from '@/components/delete-user';
import { FieldInfoLabel } from '@/components/field-info-label';
import HeadingSmall from '@/components/heading-small';
import { ImageCropDialog } from '@/components/image-crop-dialog';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Pengaturan profil',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    name: string;
    email: string;
    avatar: File | null;
    remove_avatar: boolean;
    _method: 'patch';
};

export default function Profile({
    mustVerifyEmail,
    status,
    accountDeletionEnabled,
}: {
    mustVerifyEmail: boolean;
    status?: string;
    accountDeletionEnabled: boolean;
}) {
    const { auth } = usePage<SharedData>().props;
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSource, setCropSource] = useState<File | null>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    const { data, setData, post, errors, clearErrors, processing, recentlySuccessful } = useForm<ProfileForm>({
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
        avatar: null,
        remove_avatar: false,
        _method: 'patch',
    });

    useEffect(() => {
        if (!data.avatar) {
            setPreviewUrl(null);
            return;
        }

        const url = URL.createObjectURL(data.avatar);
        setPreviewUrl(url);

        return () => URL.revokeObjectURL(url);
    }, [data.avatar]);

    if (!auth.user) {
        return null;
    }

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('profile.update'), { forceFormData: true, preserveScroll: true });
    };

    const avatarFallback = auth.user.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');

    const selectAvatar = (event: React.ChangeEvent<HTMLInputElement>) => {
        const selectedFile = event.target.files?.[0] ?? null;

        if (selectedFile) {
            setCropSource(selectedFile);
        }

        event.target.value = '';
    };

    const applyAvatarCrop = (croppedFile: File) => {
        setData((current) => ({
            ...current,
            avatar: croppedFile,
            remove_avatar: false,
        }));
        clearErrors('avatar');
        setCropSource(null);
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Pengaturan profil" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Informasi profil" description="Perbarui avatar, nama, dan alamat email akun." />

                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <FieldInfoLabel description="Gunakan JPG, PNG, atau WebP maksimal 2 MB.">Avatar</FieldInfoLabel>
                            <div className="bg-muted/20 flex flex-col gap-4 rounded-lg border p-4 sm:flex-row sm:items-center">
                                <Avatar className="size-20 border">
                                    <AvatarImage
                                        src={previewUrl ?? (!data.remove_avatar ? auth.user.avatar : undefined)}
                                        alt={`Avatar ${auth.user.name}`}
                                    />
                                    <AvatarFallback className="text-lg">{avatarFallback}</AvatarFallback>
                                </Avatar>

                                <div className="flex flex-1 flex-wrap gap-2">
                                    <Button type="button" variant="outline" onClick={() => fileInputRef.current?.click()}>
                                        <ImagePlus className="size-4" />
                                        Pilih avatar
                                    </Button>
                                    {(data.avatar || (auth.user.avatar && !data.remove_avatar)) && (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            className="text-destructive hover:text-destructive"
                                            onClick={() => {
                                                setData('avatar', null);
                                                setData('remove_avatar', true);
                                                if (fileInputRef.current) fileInputRef.current.value = '';
                                            }}
                                        >
                                            <Trash2 className="size-4" />
                                            Hapus avatar
                                        </Button>
                                    )}
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        className="sr-only"
                                        accept="image/jpeg,image/png,image/webp"
                                        onChange={selectAvatar}
                                    />
                                </div>
                            </div>
                            <InputError message={errors.avatar} />
                        </div>

                        <ImageCropDialog
                            file={cropSource}
                            open={Boolean(cropSource)}
                            onOpenChange={(open) => {
                                if (!open) setCropSource(null);
                            }}
                            onApply={applyAvatarCrop}
                        />

                        <div className="grid gap-2">
                            <FieldInfoLabel htmlFor="name" required description="Nama ini akan ditampilkan pada area akun dan aktivitas aplikasi.">
                                Nama
                            </FieldInfoLabel>
                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.name}
                                onChange={(event) => setData('name', event.target.value)}
                                required
                                autoComplete="name"
                                placeholder="Nama lengkap"
                            />
                            <InputError className="mt-2" message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <FieldInfoLabel htmlFor="email" required description="Email digunakan untuk login dan notifikasi akun.">
                                Alamat email
                            </FieldInfoLabel>
                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(event) => setData('email', event.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Alamat email"
                            />
                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="mt-2 text-sm text-neutral-800 dark:text-neutral-200">
                                    Alamat email kamu belum diverifikasi.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="rounded-md text-sm text-neutral-600 underline hover:text-neutral-900 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:text-neutral-300 dark:hover:text-neutral-100"
                                    >
                                        Klik di sini untuk mengirim ulang email verifikasi.
                                    </Link>
                                </p>
                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">Link verifikasi baru sudah dikirim ke email kamu.</div>
                                )}
                            </div>
                        )}

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Simpan</Button>
                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-muted-foreground text-sm">Tersimpan</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                {accountDeletionEnabled && <DeleteUser />}
            </SettingsLayout>
        </AppLayout>
    );
}
