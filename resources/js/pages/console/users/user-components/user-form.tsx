import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { PermissionGroup, RoleOption, UserRow } from '@/pages/console/users/types';
import { useForm } from '@inertiajs/react';
import { Crop, Send, Upload, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { ImageCropDialog } from './image-crop-dialog';

type Props = {
    user?: UserRow | null;
    roles: RoleOption[];
    permissionGroups: PermissionGroup[];
    onSuccess: () => void;
    onCancel?: () => void;
};

type UserFormData = {
    name: string;
    email: string;
    send_password_reset_link: boolean;
    avatar: File | null;
    remove_avatar: boolean;
    roles: string[];
    permissions: string[];
    _method?: string;
};

export default function UserForm({ user, roles, permissionGroups, onSuccess, onCancel }: Props) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [cropSource, setCropSource] = useState<File | null>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const form = useForm<UserFormData>({
        name: user?.name ?? '',
        email: user?.email ?? '',
        send_password_reset_link: false,
        avatar: null,
        remove_avatar: false,
        roles: user?.roles ?? [],
        permissions: user?.permissions ?? [],
    });

    useEffect(() => {
        form.setData({
            name: user?.name ?? '',
            email: user?.email ?? '',
            send_password_reset_link: false,
            avatar: null,
            remove_avatar: false,
            roles: user?.roles ?? [],
            permissions: user?.permissions ?? [],
        });
        form.clearErrors();
        setCropSource(null);
        // Form reset intentionally follows selected user changes only.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [user?.id]);

    const isEdit = Boolean(user);

    useEffect(() => {
        if (!form.data.avatar) {
            setPreviewUrl(null);
            return;
        }

        const objectUrl = URL.createObjectURL(form.data.avatar);
        setPreviewUrl(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [form.data.avatar]);

    const avatarFallback =
        form.data.name
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part.charAt(0))
            .join('')
            .toUpperCase() ||
        user?.initials ||
        'US';
    const inheritedPermissions = new Set(roles.filter((role) => form.data.roles.includes(role.name)).flatMap((role) => role.permissions));

    const toggleRole = (role: string, checked: boolean) => {
        form.setData('roles', checked ? [...form.data.roles, role] : form.data.roles.filter((name) => name !== role));
    };

    const togglePermission = (permission: string, checked: boolean) => {
        form.setData('permissions', checked ? [...form.data.permissions, permission] : form.data.permissions.filter((name) => name !== permission));
    };

    const selectAvatar = (event: React.ChangeEvent<HTMLInputElement>) => {
        const selectedFile = event.target.files?.[0] ?? null;

        if (selectedFile) {
            setCropSource(selectedFile);
        }

        event.target.value = '';
    };

    const applyAvatarCrop = (croppedFile: File) => {
        form.setData((data) => ({
            ...data,
            avatar: croppedFile,
            remove_avatar: false,
        }));
        form.clearErrors('avatar');
        setCropSource(null);
    };

    const removeAvatar = () => {
        form.setData((data) => ({
            ...data,
            avatar: null,
            remove_avatar: Boolean(user?.avatar),
        }));
        setCropSource(null);
    };

    const submit = (event: React.FormEvent) => {
        event.preventDefault();

        if (user) {
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(route('users.update', user.id), {
                forceFormData: true,
                preserveScroll: true,
                onSuccess,
            });

            return;
        }

        form.transform((data) => data);
        form.post(route('users.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="bg-muted/20 flex items-center gap-4 rounded-lg border p-3">
                <div className="relative shrink-0">
                    <Avatar className="size-20 rounded-lg">
                        <AvatarImage src={previewUrl ?? (!form.data.remove_avatar ? user?.avatar : undefined) ?? undefined} />
                        <AvatarFallback className="rounded-lg text-xl">{avatarFallback}</AvatarFallback>
                    </Avatar>
                    {(form.data.avatar || (user?.avatar && !form.data.remove_avatar)) && (
                        <Button
                            type="button"
                            variant="destructive"
                            size="icon"
                            className="border-background absolute -top-2 -right-2 size-6 rounded-full border-2"
                            title="Hapus gambar"
                            aria-label="Hapus gambar"
                            onClick={removeAvatar}
                        >
                            <X className="size-3" />
                        </Button>
                    )}
                </div>
                <div className="flex min-w-0 flex-1 flex-col gap-2">
                    <Button type="button" variant="outline" className="w-full justify-between px-3" onClick={() => fileInputRef.current?.click()}>
                        <span className="text-muted-foreground truncate">{form.data.avatar?.name ?? 'Upload avatar'}</span>
                        <Upload className="text-primary size-4" />
                    </Button>
                    {form.data.avatar && (
                        <Button type="button" variant="ghost" size="sm" className="justify-start" onClick={() => setCropSource(form.data.avatar)}>
                            <Crop className="size-4" />
                            Crop ulang
                        </Button>
                    )}
                    <input ref={fileInputRef} type="file" accept="image/*" className="sr-only" onChange={selectAvatar} />
                </div>
            </div>
            <InputError message={form.errors.avatar} />

            <ImageCropDialog
                file={cropSource}
                open={Boolean(cropSource)}
                onOpenChange={(open) => {
                    if (!open) {
                        setCropSource(null);
                    }
                }}
                onApply={applyAvatarCrop}
            />

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="name" required description="Nama lengkap yang akan tampil di tabel, preview user, dan menu profil.">
                    Name
                </FieldInfoLabel>
                <Input id="name" value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} />
                <InputError message={form.errors.name} />
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="email" required description="Email digunakan untuk login dan harus unik untuk setiap user.">
                    Email
                </FieldInfoLabel>
                <Input id="email" type="email" value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} />
                <InputError message={form.errors.email} />
            </div>

            {isEdit ? (
                <div className="bg-primary/5 text-primary border-primary/20 flex gap-3 rounded-lg border p-4">
                    <Send className="mt-0.5 size-4 shrink-0" />
                    <div className="space-y-3">
                        <div className="space-y-1">
                            <p className="text-sm font-medium">Password diatur oleh pemilik akun.</p>
                            <p className="text-muted-foreground text-xs leading-relaxed">
                                Admin tidak mengisi password manual dari User Management. Centang opsi ini jika user perlu menerima tautan untuk
                                mengatur ulang password melalui email.
                            </p>
                        </div>
                        <label className="text-foreground flex items-center gap-2 text-sm font-medium">
                            <Checkbox
                                checked={form.data.send_password_reset_link}
                                onCheckedChange={(checked) => form.setData('send_password_reset_link', Boolean(checked))}
                            />
                            Kirim tautan atur password setelah user diperbarui
                        </label>
                    </div>
                </div>
            ) : (
                <div className="bg-primary/5 text-primary border-primary/20 flex gap-3 rounded-lg border p-4">
                    <Send className="mt-0.5 size-4 shrink-0" />
                    <div className="space-y-1">
                        <p className="text-sm font-medium">Password dibuat melalui tautan aktivasi.</p>
                        <p className="text-muted-foreground text-xs leading-relaxed">
                            Saat tombol create ditekan, user dibuat dengan password internal acak lalu sistem mengirim tautan untuk aktivasi dan
                            mengatur password ke email user.
                        </p>
                    </div>
                </div>
            )}

            <div className="space-y-2">
                <FieldInfoLabel required description="Role menentukan kumpulan permission utama yang dimiliki user.">
                    Roles
                </FieldInfoLabel>
                <div className="grid gap-2 rounded-md border p-3">
                    {roles.map((role) => (
                        <label key={role.id} className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={form.data.roles.includes(role.name)}
                                onCheckedChange={(checked) => toggleRole(role.name, Boolean(checked))}
                            />
                            <span className="capitalize">{role.name}</span>
                        </label>
                    ))}
                </div>
                <InputError message={form.errors.roles} />
            </div>

            <div className="space-y-2">
                <FieldInfoLabel description="Permission dari role ikut dicentang sebagai read-only. Centang manual hanya untuk direct permission tambahan.">
                    Effective & Direct Permissions
                </FieldInfoLabel>
                <div className="max-h-[260px] overflow-y-auto rounded-md border">
                    <div className="space-y-4 p-3">
                        {permissionGroups.map((group) => (
                            <div key={group.module} className="space-y-2">
                                <p className="text-muted-foreground text-xs font-semibold uppercase">{group.module}</p>
                                <div className="grid gap-2">
                                    {group.permissions.map((permission) => (
                                        <label key={permission.id} className="flex items-center gap-2 text-sm">
                                            <Checkbox
                                                checked={form.data.permissions.includes(permission.name) || inheritedPermissions.has(permission.name)}
                                                disabled={inheritedPermissions.has(permission.name)}
                                                onCheckedChange={(checked) => togglePermission(permission.name, Boolean(checked))}
                                            />
                                            <span className="min-w-0 flex-1">{permission.name}</span>
                                            {inheritedPermissions.has(permission.name) && (
                                                <Badge variant="secondary" className="shrink-0 text-[10px]">
                                                    dari role
                                                </Badge>
                                            )}
                                        </label>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
                <p className="text-muted-foreground text-xs">
                    Permission bertanda <span className="font-medium">dari role</span> mengikuti role yang dipilih. Hapus centangnya lewat role, bukan
                    dari direct permission.
                </p>
            </div>

            <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:justify-end">
                {onCancel && (
                    <Button type="button" variant="outline" disabled={form.processing} className="h-11 min-w-28" onClick={onCancel}>
                        Cancel
                    </Button>
                )}
                <Button type="submit" disabled={form.processing} className="h-11 min-w-28">
                    {form.processing ? 'Saving...' : user ? 'Update User' : 'Create & Send Link'}
                </Button>
            </div>
        </form>
    );
}
