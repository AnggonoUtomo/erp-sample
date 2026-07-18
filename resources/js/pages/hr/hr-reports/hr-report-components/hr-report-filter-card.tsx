import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { SlidersHorizontal } from 'lucide-react';
import type { HRReportsPageProps } from '../types';

export function HRReportFilterCard({
    filters,
    options,
    onChange,
}: {
    filters: HRReportsPageProps['filters'];
    options: HRReportsPageProps['options'];
    onChange: (key: keyof HRReportsPageProps['filters'], value: string) => void;
}) {
    return (
        <Card>
            <CardHeader className="gap-1">
                <CardTitle className="flex items-center gap-2 text-lg">
                    <SlidersHorizontal className="size-4" aria-hidden="true" />
                    Filter report
                </CardTitle>
                <CardDescription>Pilih tanggal dan window masa berlaku. Semua filter hanya membaca data, tidak mengubah data HR.</CardDescription>
            </CardHeader>
            <CardContent className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div className="space-y-2">
                    <Label htmlFor="hr-report-as-of">Tanggal acuan</Label>
                    <Input id="hr-report-as-of" type="date" value={filters.as_of} onChange={(event) => onChange('as_of', event.target.value)} />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="hr-report-status-filter">Status kerja</Label>
                    <Select
                        value={filters.employment_status_id ? String(filters.employment_status_id) : 'all'}
                        onValueChange={(value) => onChange('employment_status_id', value)}
                    >
                        <SelectTrigger id="hr-report-status-filter" aria-label="Filter status kerja">
                            <SelectValue placeholder="Semua status kerja" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Semua status kerja</SelectItem>
                            {options.employmentStatuses.map((status) => (
                                <SelectItem key={status.value} value={String(status.value)}>
                                    {status.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <WindowInput
                    id="hr-report-contract-window"
                    label="Kontrak akan berakhir"
                    helper="Jumlah hari dari tanggal acuan."
                    value={filters.contract_within_days}
                    onChange={(value) => onChange('contract_within_days', value)}
                />
                <WindowInput
                    id="hr-report-document-window"
                    label="Dokumen akan kedaluwarsa"
                    helper="Jumlah hari dari tanggal acuan."
                    value={filters.document_within_days}
                    onChange={(value) => onChange('document_within_days', value)}
                />
            </CardContent>
        </Card>
    );
}

function WindowInput({
    id,
    label,
    helper,
    value,
    onChange,
}: {
    id: string;
    label: string;
    helper: string;
    value: number;
    onChange: (value: string) => void;
}) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input id={id} min={0} max={3650} type="number" value={value} onChange={(event) => onChange(event.target.value)} />
            <p className="text-muted-foreground text-xs">{helper} Default 30 hari.</p>
        </div>
    );
}
