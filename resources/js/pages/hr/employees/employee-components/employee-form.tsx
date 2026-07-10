import { FieldInfoLabel } from '@/components/field-info-label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { ImagePlus, Plus, X } from 'lucide-react';
import type { ChangeEvent, FormEvent } from 'react';
import { useEffect, useMemo, useState } from 'react';
import type { EmployeeForm as EmployeeFormData, EmployeeOptions, EmployeeRow } from '../types';

type Props = {
    form: InertiaFormProps<EmployeeFormData>;
    editing: EmployeeRow | null;
    options: EmployeeOptions;
    canCreate: boolean;
    canUpdate: boolean;
    onSubmit: (event: FormEvent) => void;
    onCancel: () => void;
};

const noneValue = '__none__';

function selectValue(value: string) {
    return value || noneValue;
}

function selectedValue(value: string) {
    return value === noneValue ? '' : value;
}

export function EmployeeForm({ form, editing, options, canCreate, canUpdate, onSubmit, onCancel }: Props) {
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);

    useEffect(() => {
        if (!form.data.avatar) {
            setPreviewUrl(null);
            return;
        }

        const objectUrl = URL.createObjectURL(form.data.avatar);
        setPreviewUrl(objectUrl);

        return () => URL.revokeObjectURL(objectUrl);
    }, [form.data.avatar]);

    const fallback = useMemo(() => (form.data.display_name || form.data.first_name || 'EM').slice(0, 2).toUpperCase(), [form.data.display_name, form.data.first_name]);

    const handleAvatar = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0] ?? null;
        form.setData({
            ...form.data,
            avatar: file,
            remove_avatar: false,
        });
    };

    const selectField = (key: keyof EmployeeFormData, value: string) => {
        form.setData(key, selectedValue(value));
    };

    return (
        <form className="space-y-4" onSubmit={onSubmit}>
            <div className="rounded-lg border p-3">
                <div className="flex items-center gap-3">
                    <Avatar className="size-16 rounded-xl">
                        <AvatarImage src={previewUrl ?? (!form.data.remove_avatar ? editing?.avatar : undefined) ?? undefined} />
                        <AvatarFallback className="rounded-xl text-lg">{fallback}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0 flex-1">
                        <FieldInfoLabel htmlFor="employee_avatar" description="Foto avatar employee. Disimpan melalui Spatie Media Library.">
                            Avatar
                        </FieldInfoLabel>
                        <label className="mt-2 flex h-10 cursor-pointer items-center gap-2 rounded-md border px-3 text-sm hover:bg-muted/50">
                            <ImagePlus className="size-4" />
                            <span className="truncate">{form.data.avatar?.name ?? 'Upload avatar'}</span>
                            <Input id="employee_avatar" type="file" accept="image/*" className="hidden" onChange={handleAvatar} />
                        </label>
                    </div>
                    {(form.data.avatar || (editing?.avatar && !form.data.remove_avatar)) && (
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon"
                            className="size-8"
                            onClick={() =>
                                form.setData({
                                    ...form.data,
                                    avatar: null,
                                    remove_avatar: Boolean(editing?.avatar),
                                })
                            }
                        >
                            <X className="size-4" />
                        </Button>
                    )}
                </div>
                {form.errors.avatar && <p className="mt-2 text-sm text-destructive">{form.errors.avatar}</p>}
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="employee_number" required description="Nomor unik employee untuk payroll, attendance, dan dokumen HR.">
                        Employee Number
                    </FieldInfoLabel>
                    <Input id="employee_number" value={form.data.employee_number} onChange={(event) => form.setData('employee_number', event.target.value)} placeholder="EMP-0001" />
                    {form.errors.employee_number && <p className="text-sm text-destructive">{form.errors.employee_number}</p>}
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="display_name" description="Nama tampil. Jika kosong, backend memakai gabungan nama depan dan belakang.">
                        Display Name
                    </FieldInfoLabel>
                    <Input id="display_name" value={form.data.display_name} onChange={(event) => form.setData('display_name', event.target.value)} placeholder="Ayu Prameswari" />
                    {form.errors.display_name && <p className="text-sm text-destructive">{form.errors.display_name}</p>}
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="first_name" required description="Nama depan employee untuk identitas HR.">
                        First Name
                    </FieldInfoLabel>
                    <Input id="first_name" value={form.data.first_name} onChange={(event) => form.setData('first_name', event.target.value)} placeholder="Ayu" />
                    {form.errors.first_name && <p className="text-sm text-destructive">{form.errors.first_name}</p>}
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="last_name" description="Nama belakang employee jika ada.">
                        Last Name
                    </FieldInfoLabel>
                    <Input id="last_name" value={form.data.last_name} onChange={(event) => form.setData('last_name', event.target.value)} placeholder="Prameswari" />
                    {form.errors.last_name && <p className="text-sm text-destructive">{form.errors.last_name}</p>}
                </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="work_email" description="Email kerja employee. Harus unik jika diisi.">
                        Work Email
                    </FieldInfoLabel>
                    <Input id="work_email" value={form.data.work_email} onChange={(event) => form.setData('work_email', event.target.value)} placeholder="ayu@company.test" />
                    {form.errors.work_email && <p className="text-sm text-destructive">{form.errors.work_email}</p>}
                </div>
                <div className="space-y-2">
                    <FieldInfoLabel htmlFor="phone" description="Nomor telepon utama employee.">
                        Phone
                    </FieldInfoLabel>
                    <Input id="phone" value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} placeholder="+62812..." />
                    {form.errors.phone && <p className="text-sm text-destructive">{form.errors.phone}</p>}
                </div>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <Select value={selectValue(form.data.user_id)} onValueChange={(value) => selectField('user_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Opsional. Hubungkan employee dengan akun login Console/HR.">User Login</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih user" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Tidak terhubung</SelectItem>
                        {options.users.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={selectValue(form.data.departement_id)} onValueChange={(value) => selectField('departement_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Departement employee untuk struktur organisasi, reporting, dan filter HR.">Departement</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih departement" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.departements.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={selectValue(form.data.position_id)} onValueChange={(value) => selectField('position_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Jabatan employee untuk approval, payroll, dan report headcount.">Position</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih position" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.positions.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={selectValue(form.data.job_level_id)} onValueChange={(value) => selectField('job_level_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Level/grade employee untuk benefit, approval, dan payroll policy.">Job Level</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih job level" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.jobLevels.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
                <Select value={selectValue(form.data.work_location_id)} onValueChange={(value) => selectField('work_location_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Lokasi kerja utama untuk attendance, geofence, dan timezone.">Work Location</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih lokasi" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.workLocations.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={selectValue(form.data.employment_status_id)} onValueChange={(value) => selectField('employment_status_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel required description="Status kerja employee, misalnya permanent, probation, resigned.">
                            Employment Status
                        </FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih status" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.employmentStatuses.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <Select value={selectValue(form.data.employment_type_id)} onValueChange={(value) => selectField('employment_type_id', value)}>
                    <div className="space-y-2">
                        <FieldInfoLabel description="Tipe hubungan kerja, misalnya permanent, contract, intern, freelance.">Employment Type</FieldInfoLabel>
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih type" />
                        </SelectTrigger>
                    </div>
                    <SelectContent>
                        <SelectItem value={noneValue}>Belum dipilih</SelectItem>
                        {options.employmentTypes.map((option) => (
                            <SelectItem key={option.value} value={String(option.value)}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <div className="grid gap-3 sm:grid-cols-2">
                    <div className="space-y-2">
                        <FieldInfoLabel htmlFor="hired_at" description="Tanggal mulai kerja.">Join Date</FieldInfoLabel>
                        <Input id="hired_at" type="date" value={form.data.hired_at} onChange={(event) => form.setData('hired_at', event.target.value)} />
                    </div>
                    <div className="space-y-2">
                        <FieldInfoLabel htmlFor="ended_at" description="Tanggal akhir kerja jika employee sudah selesai bekerja.">End Date</FieldInfoLabel>
                        <Input id="ended_at" type="date" value={form.data.ended_at} onChange={(event) => form.setData('ended_at', event.target.value)} />
                    </div>
                </div>
            </div>

            <div className="space-y-2">
                <FieldInfoLabel htmlFor="notes" description="Catatan internal HR untuk employee ini.">
                    Notes
                </FieldInfoLabel>
                <textarea
                    id="notes"
                    value={form.data.notes}
                    onChange={(event) => form.setData('notes', event.target.value)}
                    rows={3}
                    className="min-h-20 w-full resize-y rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                />
                {form.errors.notes && <p className="text-sm text-destructive">{form.errors.notes}</p>}
            </div>

            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                <span>
                    <span className="block font-medium">Employee aktif</span>
                    <span className="mt-1 block text-xs leading-5 text-muted-foreground">Employee aktif bisa dipakai oleh Attendance, Payroll, dan module HR lanjutan.</span>
                </span>
            </label>

            <div className="flex gap-2">
                {(editing ? canUpdate : canCreate) && (
                    <Button type="submit" disabled={form.processing} className="flex-1">
                        <Plus className="size-4" />
                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Perubahan' : 'Tambah Employee'}
                    </Button>
                )}
                {editing && (
                    <Button type="button" variant="outline" onClick={onCancel}>
                        Batal
                    </Button>
                )}
            </div>
        </form>
    );
}
