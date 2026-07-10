import { PaginationBar } from '@/components/pagination-bar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { BriefcaseBusiness, Edit3, Plus, Search, Trash2 } from 'lucide-react';
import type { DepartementOption, Paginator, PositionRow } from '../types';

type Props = {
    positions: Paginator<PositionRow>;
    departementOptions: DepartementOption[];
    search: string;
    status: string;
    departement: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onDepartementChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (position: PositionRow) => void;
    onEdit: (position: PositionRow) => void;
    onDelete: (position: PositionRow) => void;
};

export function PositionTable({
    positions,
    departementOptions,
    search,
    status,
    departement,
    canCreate,
    canUpdate,
    canDelete,
    onSearchChange,
    onStatusChange,
    onDepartementChange,
    onAdd,
    onSelect,
    onEdit,
    onDelete,
}: Props) {
    return (
        <div className="w-full min-w-0 space-y-4">
            <div className="space-y-2.5">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <h2 className="text-lg font-semibold tracking-tight">Position Directory</h2>
                        <p className="mt-0.5 text-sm text-muted-foreground">Cari, filter, dan kelola master jabatan per departement.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {positions.total} total
                    </Badge>
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="position-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari position"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={departement} onValueChange={onDepartementChange}>
                        <SelectTrigger id="position-departement-filter-trigger" className="h-10 w-full md:w-[190px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Departements</SelectItem>
                            {departementOptions.map((option) => (
                                <SelectItem key={option.id} value={String(option.id)}>
                                    {option.code} - {option.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="position-status-filter-trigger" className="h-10 w-full md:w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                {canCreate && (
                    <Button onClick={onAdd} className="h-11 w-full gap-2 sm:w-auto">
                        <Plus className="size-4" />
                        Tambah Position
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[580px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-left text-muted-foreground">
                            <tr>
                                <th className="w-[38%] px-2.5 py-2.5 font-semibold">Position</th>
                                <th className="w-[34%] px-2.5 py-2.5 font-semibold">Departement</th>
                                <th className="w-[14%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[14%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {positions.data.map((row, index) => (
                                <tr key={row.id} className="border-t transition hover:bg-muted/40">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`position-table-row-${index}`}
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
                                            <span className="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                                <BriefcaseBusiness className="size-4" />
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.name}</span>
                                                <span className="mt-0.5 block truncate text-xs text-muted-foreground">Kode: {row.code}</span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        {row.departement ? (
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">{row.departement.name}</div>
                                                <div className="truncate text-xs text-muted-foreground">Kode departement: {row.departement.code}</div>
                                            </div>
                                        ) : (
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">Belum terhubung</div>
                                                <div className="truncate text-xs text-muted-foreground">Pilih departement pada form</div>
                                            </div>
                                        )}
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <Badge className={row.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                            {row.active ? 'Aktif' : 'Nonaktif'}
                                        </Badge>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <div className="flex justify-end gap-1">
                                            {canUpdate && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" title={`Edit ${row.name}`} onClick={() => onEdit(row)}>
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8 text-destructive hover:text-destructive"
                                                    title={`Hapus ${row.name}`}
                                                    onClick={() => onDelete(row)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!positions.data.length && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-10 text-center text-muted-foreground">
                                        Belum ada Position yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-sm text-muted-foreground">
                    Showing {positions.data.length} of {positions.total} positions
                </div>
                <PaginationBar
                    meta={positions}
                    routeName="hr.positions.index"
                    filters={{ search, status, departement }}
                    idPrefix="positions"
                />
            </div>
        </div>
    );
}
