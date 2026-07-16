import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { router, useForm } from '@inertiajs/react';
import type { CodeName, EmployeeOption, IdName, OffboardingFilterForm } from '../types';
import { offboardingStatusLabel } from './offboarding-presenters';

type FilterOption = { value: string; label: string };

type Props = {
    businessDate: string;
    employeeOptions: EmployeeOption[];
    templateOptions: CodeName[];
    ownerOptions: IdName[];
    filters: Partial<OffboardingFilterForm>;
};

const exitTypeOptions: FilterOption[] = [
    { value: 'RESIGNATION', label: 'Pengunduran diri' },
    { value: 'TERMINATION', label: 'Pemutusan hubungan kerja' },
    { value: 'END_OF_CONTRACT', label: 'Kontrak berakhir' },
    { value: 'RETIREMENT', label: 'Pensiun' },
    { value: 'OTHER', label: 'Lainnya' },
];

export function OffboardingFilterCard({ businessDate, employeeOptions, templateOptions, ownerOptions, filters }: Props) {
    const form = useForm<OffboardingFilterForm>({
        employee_id: String(filters.employee_id ?? ''),
        owner_user_id: String(filters.owner_user_id ?? ''),
        template_id: String(filters.template_id ?? ''),
        status: String(filters.status ?? ''),
        exit_type: String(filters.exit_type ?? ''),
        exit_from: String(filters.exit_from ?? ''),
        exit_to: String(filters.exit_to ?? ''),
        due: Boolean(filters.due),
        overdue: Boolean(filters.overdue),
        archived: Boolean(filters.archived),
        business_date: businessDate,
    });
    const errors = Object.values(form.errors);

    return (
        <Card>
            <CardContent className="p-4">
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.get(route('hr.offboardings.index'), { preserveState: true, preserveScroll: true });
                    }}
                    className="grid gap-3 md:grid-cols-3 xl:grid-cols-5"
                >
                    <FilterSelect
                        value={form.data.employee_id}
                        onChange={(value) => form.setData('employee_id', value)}
                        placeholder="Semua employee"
                        options={employeeOptions.map((item) => ({ value: String(item.id), label: item.display_name }))}
                    />
                    <FilterSelect
                        value={form.data.status}
                        onChange={(value) => form.setData('status', value)}
                        placeholder="Semua status"
                        options={['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT', 'COMPLETED', 'CANCELLED'].map((value) => ({
                            value,
                            label: offboardingStatusLabel(value),
                        }))}
                    />
                    <FilterSelect
                        value={form.data.exit_type}
                        onChange={(value) => form.setData('exit_type', value)}
                        placeholder="Semua exit type"
                        options={exitTypeOptions}
                    />
                    <FilterSelect
                        value={form.data.template_id}
                        onChange={(value) => form.setData('template_id', value)}
                        placeholder="Semua template"
                        options={templateOptions.map((item) => ({ value: String(item.id), label: item.name }))}
                    />
                    <FilterSelect
                        value={form.data.owner_user_id}
                        onChange={(value) => form.setData('owner_user_id', value)}
                        placeholder="Semua owner"
                        options={ownerOptions.map((item) => ({ value: String(item.id), label: item.name }))}
                    />
                    <Input
                        type="date"
                        aria-label="Business date"
                        value={form.data.business_date}
                        onChange={(event) => form.setData('business_date', event.target.value)}
                    />
                    <Input
                        type="date"
                        aria-label="Exit date dari"
                        value={form.data.exit_from}
                        onChange={(event) => form.setData('exit_from', event.target.value)}
                    />
                    <Input
                        type="date"
                        aria-label="Exit date sampai"
                        value={form.data.exit_to}
                        onChange={(event) => form.setData('exit_to', event.target.value)}
                    />
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={form.data.due} onChange={(event) => form.setData('due', event.target.checked)} /> Due
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={form.data.overdue} onChange={(event) => form.setData('overdue', event.target.checked)} />{' '}
                        Overdue
                    </label>
                    <label className="flex items-center gap-2 text-sm">
                        <input type="checkbox" checked={form.data.archived} onChange={(event) => form.setData('archived', event.target.checked)} />{' '}
                        Histori arsip
                    </label>
                    <div className="flex gap-2">
                        <Button type="submit" size="sm">
                            Terapkan
                        </Button>
                        <Button type="button" size="sm" variant="outline" onClick={() => router.get(route('hr.offboardings.index'))}>
                            Reset
                        </Button>
                    </div>
                </form>
                {errors.length > 0 && (
                    <div role="alert" className="text-destructive mt-3 text-sm">
                        Filter belum dapat diterapkan: {errors.join(' ')}
                    </div>
                )}
            </CardContent>
        </Card>
    );
}

function FilterSelect({
    value,
    onChange,
    placeholder,
    options,
}: {
    value: string;
    onChange: (value: string) => void;
    placeholder: string;
    options: FilterOption[];
}) {
    return (
        <Select value={value || 'all'} onValueChange={(next) => onChange(next === 'all' ? '' : next)}>
            <SelectTrigger aria-label={placeholder}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="all">{placeholder}</SelectItem>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
