import { FieldInfoLabel } from '@/components/field-info-label';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { InertiaFormProps } from '@inertiajs/react';
import { ListFilter, Save, Send } from 'lucide-react';
import type { FormEvent } from 'react';
import { perPageOptions } from '../options';
import type { PaginationForm, PaginationSettings } from '../types';

type Props = {
    can: { update: boolean };
    paginationSettings: PaginationSettings;
    form: InertiaFormProps<PaginationForm>;
    togglePerPageOption: (option: number, checked: boolean) => void;
    submit: (event: FormEvent) => void;
};

export function PaginationSettingsPanel({ can, paginationSettings, form, togglePerPageOption, submit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <CardTitle className="flex items-center gap-2">
                    <span className="dashboard-icon icon-tone-emerald flex size-10 items-center justify-center rounded-md">
                        <ListFilter className="size-5" />
                    </span>
                    Default Pagination
                </CardTitle>
                <CardDescription>Atur jumlah row default dan opsi pilihan row pada tabel aplikasi.</CardDescription>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                <form onSubmit={submit} className="space-y-6">
                    <div className="grid gap-4 lg:grid-cols-3">
                        <SummaryTile label="Default Row" value={paginationSettings.default_per_page} />
                        <SummaryTile label="Opsi Aktif" value={paginationSettings.per_page_options.join(', ')} />
                        <SummaryTile label="Dipakai Oleh" value="Table Pagination" />
                    </div>

                    <div className="grid gap-4 lg:grid-cols-[minmax(0,1fr)_260px]">
                        <div className="space-y-3">
                            <FieldInfoLabel required description="Pilih opsi jumlah row yang boleh muncul di pagination bar.">
                                Opsi Jumlah Row
                            </FieldInfoLabel>
                            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                {perPageOptions.map((option) => (
                                    <label key={option} className="bg-background/60 flex items-center gap-3 rounded-lg border p-3">
                                        <Checkbox
                                            checked={form.data.per_page_options.includes(option)}
                                            disabled={!can.update || form.processing}
                                            onCheckedChange={(value) => togglePerPageOption(option, Boolean(value))}
                                        />
                                        <span className="text-sm font-medium">{option} rows</span>
                                    </label>
                                ))}
                            </div>
                            <InputError message={form.errors.per_page_options} />
                        </div>

                        <div className="space-y-2">
                            <FieldInfoLabel required description="Jumlah row awal saat halaman tabel dibuka tanpa parameter per_page.">
                                Default Row
                            </FieldInfoLabel>
                            <Select
                                value={form.data.default_per_page}
                                onValueChange={(value) => form.setData('default_per_page', value)}
                                disabled={!can.update || form.processing || form.data.per_page_options.length === 0}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih default" />
                                </SelectTrigger>
                                <SelectContent>
                                    {form.data.per_page_options.map((option) => (
                                        <SelectItem key={option} value={String(option)}>
                                            {option} rows
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.default_per_page} />
                        </div>
                    </div>

                    <div className="rounded-lg border border-dashed p-4">
                        <div className="flex items-start gap-3">
                            <span className="dashboard-icon icon-tone-sky flex size-9 shrink-0 items-center justify-center rounded-md">
                                <ListFilter className="size-4" />
                            </span>
                            <div>
                                <p className="text-sm font-medium">Efek Global</p>
                                <p className="text-muted-foreground mt-1 text-xs leading-relaxed">
                                    User Management dan Audit Logs akan memakai default ini saat URL tidak memiliki parameter{' '}
                                    <span className="font-medium">per_page</span>. Jika user memilih row manual, pilihan tersebut tetap dibawa di
                                    query string.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-col-reverse gap-2 border-t pt-5 sm:flex-row sm:items-center sm:justify-end">
                        <Button type="submit" disabled={!can.update || form.processing} className="h-11 min-w-44">
                            {form.processing ? (
                                <>
                                    <Send className="size-4 animate-pulse" />
                                    Menyimpan...
                                </>
                            ) : (
                                <>
                                    <Save className="size-4" />
                                    Simpan Pagination
                                </>
                            )}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}

function SummaryTile({ label, value }: { label: string; value: string | number }) {
    return (
        <div className="bg-background/60 rounded-lg border p-4">
            <p className="text-muted-foreground text-xs">{label}</p>
            <p className="mt-2 text-lg font-semibold">{value}</p>
        </div>
    );
}
