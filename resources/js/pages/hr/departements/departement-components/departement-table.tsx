import { PaginationBar } from '@/components/pagination-bar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Building2, Edit3, Plus, Search, Trash2 } from 'lucide-react';
import type { DepartementRow, Paginator } from '../types';

type Props = {
    departements: Paginator<DepartementRow>;
    search: string;
    status: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (departement: DepartementRow) => void;
    onEdit: (departement: DepartementRow) => void;
    onDelete: (departement: DepartementRow) => void;
};

export function DepartementTable({
    departements,
    search,
    status,
    canCreate,
    canUpdate,
    canDelete,
    onSearchChange,
    onStatusChange,
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
                        <h2 className="text-lg font-semibold tracking-tight">Departement Directory</h2>
                        <p className="text-muted-foreground mt-0.5 text-sm">Cari, filter, dan kelola struktur unit organisasi HR.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {departements.total} total
                    </Badge>
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            id="departement-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari departement"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="departement-status-filter-trigger" className="h-10 w-full md:w-[140px]">
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
                        Tambah Departement
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[560px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left">
                            <tr>
                                <th className="w-[38%] px-2.5 py-2.5 font-semibold">Departement</th>
                                <th className="w-[34%] px-2.5 py-2.5 font-semibold">Parent</th>
                                <th className="w-[14%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[14%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {departements.data.map((row, index) => (
                                <tr key={row.id} className="hover:bg-muted/40 border-t transition">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`departement-table-row-${index}`}
                                            type="button"
                                            onClick={() => onSelect(row)}
                                            onKeyDown={(event) => {
                                                if (event.key === 'Enter') {
                                                    event.preventDefault();
                                                    onSelect(row);
                                                }
                                            }}
                                            className="focus-visible:ring-ring flex min-w-0 items-center gap-2.5 rounded-md text-left outline-none focus-visible:ring-2 focus-visible:ring-offset-2"
                                        >
                                            <span className="bg-primary/10 text-primary flex size-9 shrink-0 items-center justify-center rounded-lg">
                                                <Building2 className="size-4" />
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.name}</span>
                                                <span className="text-muted-foreground mt-0.5 block truncate text-xs">Kode: {row.code}</span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        {row.parent ? (
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">{row.parent.name}</div>
                                                <div className="text-muted-foreground truncate text-xs">Kode parent: {row.parent.code}</div>
                                            </div>
                                        ) : (
                                            <div className="min-w-0">
                                                <div className="truncate font-medium">Root departement</div>
                                                <div className="text-muted-foreground truncate text-xs">Tidak punya parent</div>
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
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8"
                                                    title={`Edit ${row.name}`}
                                                    onClick={() => onEdit(row)}
                                                >
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive size-8"
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
                            {!departements.data.length && (
                                <tr>
                                    <td colSpan={4} className="text-muted-foreground px-4 py-10 text-center">
                                        Belum ada Departement yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-muted-foreground text-sm">
                    Showing {departements.data.length} of {departements.total} departements
                </div>
                <PaginationBar meta={departements} routeName="hr.departements.index" filters={{ search, status }} idPrefix="departements" />
            </div>
        </div>
    );
}
