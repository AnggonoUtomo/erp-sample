import { PaginationBar } from '@/components/pagination-bar';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArchiveRestore, Edit3, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import type { EmployeeOption, EmployeeRow, Paginator } from '../types';

type Props = {
    employees: Paginator<EmployeeRow>;
    departementOptions: EmployeeOption[];
    search: string;
    departement: string;
    status: string;
    archive: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    canRestore: boolean;
    canForceDelete: boolean;
    onSearchChange: (value: string) => void;
    onDepartementChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchiveChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (employee: EmployeeRow) => void;
    onEdit: (employee: EmployeeRow) => void;
    onDelete: (employee: EmployeeRow) => void;
    onRestore: (employee: EmployeeRow) => void;
};

export function EmployeeTable({
    employees,
    departementOptions,
    search,
    departement,
    status,
    archive,
    canCreate,
    canUpdate,
    canDelete,
    canRestore,
    canForceDelete,
    onSearchChange,
    onDepartementChange,
    onStatusChange,
    onArchiveChange,
    onAdd,
    onSelect,
    onEdit,
    onDelete,
    onRestore,
}: Props) {
    return (
        <div className="w-full min-w-0 space-y-4">
            <div className="space-y-2.5">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-semibold tracking-tight">Employee Directory</h2>
                        <p className="mt-0.5 text-sm text-muted-foreground">Cari, filter, dan kelola master employee HR.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {employees.total} total
                    </Badge>
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="employee-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari Employee"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={departement} onValueChange={onDepartementChange}>
                        <SelectTrigger id="employee-departement-filter-trigger" className="h-10 w-full md:w-[160px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Departements</SelectItem>
                            {departementOptions.map((option) => (
                                <SelectItem key={option.value} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="employee-status-filter-trigger" className="h-10 w-full md:w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={archive} onValueChange={onArchiveChange}>
                        <SelectTrigger id="employee-archive-filter-trigger" className="h-10 w-full md:w-[150px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Aktif saja</SelectItem>
                            <SelectItem value="with-trashed">Dengan arsip</SelectItem>
                            <SelectItem value="only-trashed">Arsip saja</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {canCreate && (
                    <Button onClick={onAdd} className="h-11 w-full gap-2 sm:w-auto">
                        <Plus className="size-4" />
                        Tambah Employee
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[720px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-left text-muted-foreground">
                            <tr>
                                <th className="w-[36%] px-2.5 py-2.5 font-semibold">Employee</th>
                                <th className="w-[24%] px-2.5 py-2.5 font-semibold">Organisasi</th>
                                <th className="w-[18%] px-2.5 py-2.5 font-semibold">Work Data</th>
                                <th className="w-[10%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[12%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {employees.data.map((row, index) => (
                                <tr key={row.id} className="border-t transition hover:bg-muted/40">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`employee-table-row-${index}`}
                                            type="button"
                                            onClick={() => onSelect(row)}
                                            onKeyDown={(event) => {
                                                if (event.key === 'Enter') {
                                                    event.preventDefault();
                                                    onSelect(row);
                                                }
                                            }}
                                            className="flex min-w-0 items-center gap-2.5 rounded-md text-left outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        >
                                            <Avatar className="size-10 rounded-lg">
                                                <AvatarImage src={row.avatar ?? undefined} alt={row.display_name} />
                                                <AvatarFallback className="rounded-lg">{row.display_name.slice(0, 2).toUpperCase()}</AvatarFallback>
                                            </Avatar>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.display_name}</span>
                                                <span className="mt-0.5 block truncate text-xs text-muted-foreground">
                                                    {row.employee_number} | {row.work_email ?? row.personal_email ?? 'Email belum diisi'}
                                                </span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <p className="truncate font-medium">{row.departement?.name ?? 'Departement belum dipilih'}</p>
                                        <p className="mt-0.5 truncate text-xs text-muted-foreground">{row.position?.name ?? 'Position belum dipilih'}</p>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <p className="truncate">{row.employment_status?.name ?? 'Status belum dipilih'}</p>
                                        <p className="mt-0.5 truncate text-xs text-muted-foreground">{row.work_location?.name ?? 'Lokasi belum dipilih'}</p>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        {row.deleted_at ? (
                                            <Badge className="bg-amber-600 text-white">Arsip</Badge>
                                        ) : (
                                            <Badge className={row.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                                {row.active ? 'Aktif' : 'Nonaktif'}
                                            </Badge>
                                        )}
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <div className="flex justify-end gap-1">
                                            {!row.deleted_at && canUpdate && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" title={`Edit ${row.display_name}`} onClick={() => onEdit(row)}>
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {row.deleted_at && canRestore && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" title={`Pulihkan ${row.display_name}`} onClick={() => onRestore(row)}>
                                                    <RotateCcw className="size-4" />
                                                </Button>
                                            )}
                                            {!row.deleted_at && canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8 text-destructive hover:text-destructive"
                                                    title={`Arsipkan ${row.display_name}`}
                                                    onClick={() => onDelete(row)}
                                                >
                                                    <ArchiveRestore className="size-4" />
                                                </Button>
                                            )}
                                            {row.deleted_at && canForceDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8 text-destructive hover:text-destructive"
                                                    title={`Hapus permanen ${row.display_name}`}
                                                    onClick={() => onDelete(row)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!employees.data.length && (
                                <tr>
                                    <td colSpan={5} className="px-4 py-10 text-center text-muted-foreground">
                                        Belum ada Employee yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-sm text-muted-foreground">
                    Showing {employees.data.length} of {employees.total} Employees
                </div>
                <PaginationBar meta={employees} routeName="hr.employees.index" filters={{ search, departement, status, archive }} idPrefix="employee" />
            </div>
        </div>
    );
}
