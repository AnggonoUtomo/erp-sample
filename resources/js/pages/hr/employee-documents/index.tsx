import { PaginationBar } from '@/components/pagination-bar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { ScrollText } from 'lucide-react';
import type { FormEvent } from 'react';
import type { DocumentForm, EmployeeDocumentPageProps } from './types';

export default function EmployeeDocumentsIndex({ documents, options, filters }: EmployeeDocumentPageProps) {
    const { canAny } = usePermission();
    const canCreate = canAny(['employee-documents.create', 'employee-documents.manage']);
    const form = useForm<DocumentForm>({
        employee_id: '',
        document_type_id: '',
        document_number: '',
        issuer: '',
        issued_at: '',
        expires_at: '',
        notes: '',
    });
    const selectedType = options.documentTypes.find((type) => type.value === Number(form.data.document_type_id));
    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.employee-documents.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };
    const filter = (key: keyof EmployeeDocumentPageProps['filters'], value: string) => {
        router.get(
            route('hr.employee-documents.index'),
            { ...filters, [key]: value === 'all' ? '' : value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'HR', href: '/hr/dashboard' },
                { title: 'Employee Documents', href: '/hr/employee-documents' },
            ]}
        >
            <Head title="Employee Documents" />
            <div className="mx-auto grid w-full max-w-7xl gap-6 p-4 sm:p-6 xl:grid-cols-3">
                <Card className="xl:col-span-2">
                    <CardHeader className="gap-4">
                        <CardTitle className="flex items-center gap-2">
                            <ScrollText className="size-5" /> Metadata dokumen employee
                        </CardTitle>
                        <div className="grid gap-2 sm:grid-cols-3">
                            <FilterSelect
                                label="Filter employee"
                                value={filters.employee ? String(filters.employee) : 'all'}
                                options={options.employees}
                                onChange={(value) => filter('employee', value)}
                            />
                            <FilterSelect
                                label="Filter tipe"
                                value={filters.document_type ? String(filters.document_type) : 'all'}
                                options={options.documentTypes}
                                onChange={(value) => filter('document_type', value)}
                            />
                            <FilterSelect
                                label="Filter status"
                                value={filters.status || 'all'}
                                options={[
                                    { value: 'PENDING', label: 'Pending' },
                                    { value: 'VERIFIED', label: 'Verified' },
                                    { value: 'REJECTED', label: 'Rejected' },
                                ]}
                                onChange={(value) => filter('status', value)}
                            />
                        </div>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {documents.data.length === 0 ? (
                            <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">
                                Belum ada metadata dokumen yang sesuai filter.
                            </div>
                        ) : (
                            <div className="overflow-x-auto rounded-lg border">
                                <table className="w-full text-sm">
                                    <thead className="bg-muted/50 text-left">
                                        <tr>
                                            <th className="p-3 font-medium">Employee</th>
                                            <th className="p-3 font-medium">Dokumen</th>
                                            <th className="p-3 font-medium">Tanggal</th>
                                            <th className="p-3 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {documents.data.map((document) => (
                                            <tr key={document.id} className="border-t align-top">
                                                <td className="p-3">
                                                    <p className="font-medium">{document.employee.display_name}</p>
                                                    <p className="text-muted-foreground text-xs">{document.employee.employee_number}</p>
                                                </td>
                                                <td className="p-3">
                                                    <p className="font-medium">{document.document_type.name}</p>
                                                    <p className="text-muted-foreground font-mono text-xs">
                                                        {document.document_number_masked ?? 'Tanpa nomor'}
                                                    </p>
                                                    {document.issuer && <p className="text-muted-foreground text-xs">Penerbit: {document.issuer}</p>}
                                                </td>
                                                <td className="p-3 text-xs">
                                                    <p>Terbit: {document.issued_at ?? '—'}</p>
                                                    <p>Kedaluwarsa: {document.expires_at ?? '—'}</p>
                                                </td>
                                                <td className="p-3">
                                                    <span className="bg-muted rounded-full px-2.5 py-1 text-xs font-medium">
                                                        {document.verification_status}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        <PaginationBar
                            meta={documents}
                            routeName="hr.employee-documents.index"
                            filters={{
                                employee: filters.employee || undefined,
                                document_type: filters.document_type || undefined,
                                status: filters.status || undefined,
                            }}
                            idPrefix="employee-documents"
                        />
                    </CardContent>
                </Card>

                {canCreate && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Tambah metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form className="space-y-4" onSubmit={submit}>
                                <FormSelect
                                    label="Employee"
                                    value={form.data.employee_id}
                                    options={options.employees}
                                    onChange={(value) => form.setData('employee_id', value)}
                                    error={form.errors.employee_id}
                                />
                                <FormSelect
                                    label="Tipe dokumen"
                                    value={form.data.document_type_id}
                                    options={options.documentTypes}
                                    onChange={(value) => form.setData('document_type_id', value)}
                                    error={form.errors.document_type_id}
                                />
                                <FormInput
                                    label={selectedType?.requires_number ? 'Nomor dokumen *' : 'Nomor dokumen'}
                                    value={form.data.document_number}
                                    onChange={(value) => form.setData('document_number', value)}
                                    error={form.errors.document_number}
                                    autoComplete="off"
                                />
                                <FormInput
                                    label="Penerbit"
                                    value={form.data.issuer}
                                    onChange={(value) => form.setData('issuer', value)}
                                    error={form.errors.issuer}
                                />
                                <div className="grid gap-4 sm:grid-cols-2">
                                    <FormInput
                                        label="Tanggal terbit"
                                        type="date"
                                        value={form.data.issued_at}
                                        onChange={(value) => form.setData('issued_at', value)}
                                        error={form.errors.issued_at}
                                    />
                                    <FormInput
                                        label={selectedType?.requires_expiry ? 'Kedaluwarsa *' : 'Kedaluwarsa'}
                                        type="date"
                                        value={form.data.expires_at}
                                        onChange={(value) => form.setData('expires_at', value)}
                                        error={form.errors.expires_at}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="employee-document-notes">Catatan internal</Label>
                                    <textarea
                                        id="employee-document-notes"
                                        className="bg-background min-h-20 w-full rounded-md border px-3 py-2 text-sm"
                                        value={form.data.notes}
                                        onChange={(event) => form.setData('notes', event.target.value)}
                                    />
                                    {form.errors.notes && <p className="text-destructive text-xs">{form.errors.notes}</p>}
                                </div>
                                <p className="text-muted-foreground text-xs leading-5">
                                    Slice ini hanya menyimpan metadata. File akan dikelola oleh Document Management pada task integrasi terpisah.
                                </p>
                                <Button className="w-full" disabled={form.processing}>
                                    Simpan metadata
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}

type SelectOption = { value: string | number; label: string };

function FilterSelect({
    label,
    value,
    options,
    onChange,
}: {
    label: string;
    value: string;
    options: SelectOption[];
    onChange: (value: string) => void;
}) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger aria-label={label}>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">Semua</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={String(option.value)}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function FormSelect({
    label,
    value,
    options,
    onChange,
    error,
}: {
    label: string;
    value: string;
    options: SelectOption[];
    onChange: (value: string) => void;
    error?: string;
}) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger>
                    <SelectValue placeholder="Pilih..." />
                </SelectTrigger>
                <SelectContent>
                    {options.map((option) => (
                        <SelectItem key={option.value} value={String(option.value)}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}

function FormInput({
    label,
    value,
    onChange,
    error,
    type = 'text',
    autoComplete,
}: {
    label: string;
    value: string;
    onChange: (value: string) => void;
    error?: string;
    type?: string;
    autoComplete?: string;
}) {
    const id = `employee-document-${label.toLowerCase().replace(/[^a-z]+/g, '-')}`;
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input id={id} type={type} value={value} autoComplete={autoComplete} onChange={(event) => onChange(event.target.value)} />
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}
