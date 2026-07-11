import { PaginationBar } from '@/components/pagination-bar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArchiveRestore, Edit3, ListFilter, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import type { Paginator, ReferenceDataOption, ReferenceDataRow } from '../types';

type Props = {
    referenceData: Paginator<ReferenceDataRow>;
    categoryOptions: ReferenceDataOption[];
    search: string;
    category: string;
    status: string;
    archive: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    canRestore: boolean;
    canForceDelete: boolean;
    onSearchChange: (value: string) => void;
    onCategoryChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchiveChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (referenceData: ReferenceDataRow) => void;
    onEdit: (referenceData: ReferenceDataRow) => void;
    onDelete: (referenceData: ReferenceDataRow) => void;
    onRestore: (referenceData: ReferenceDataRow) => void;
};

function categoryLabel(value: string) {
    return value.replaceAll('-', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export function HRReferenceDataTable({
    referenceData,
    categoryOptions,
    search,
    category,
    status,
    archive,
    canCreate,
    canUpdate,
    canDelete,
    canRestore,
    canForceDelete,
    onSearchChange,
    onCategoryChange,
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
                        <h2 className="text-lg font-semibold tracking-tight">Reference Data Directory</h2>
                        <p className="text-muted-foreground mt-0.5 text-sm">Cari, filter, dan kelola pilihan referensi lintas modul HR.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {referenceData.total} total
                    </Badge>
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            id="hr-reference-data-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari Reference Data"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={category} onValueChange={onCategoryChange}>
                        <SelectTrigger id="hr-reference-data-category-filter-trigger" className="h-10 w-full md:w-[170px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Kategori</SelectItem>
                            {categoryOptions.map((option) => (
                                <SelectItem key={option.value} value={option.value}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="hr-reference-data-status-filter-trigger" className="h-10 w-full md:w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={archive} onValueChange={onArchiveChange}>
                        <SelectTrigger id="hr-reference-data-archive-filter-trigger" className="h-10 w-full md:w-[150px]">
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
                        Tambah Reference Data
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[560px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left">
                            <tr>
                                <th className="w-[44%] px-2.5 py-2.5 font-semibold">Reference Data</th>
                                <th className="w-[22%] px-2.5 py-2.5 font-semibold">Kategori</th>
                                <th className="w-[16%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[18%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {referenceData.data.map((row, index) => (
                                <tr key={row.id} className="hover:bg-muted/40 border-t transition">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`hr-reference-data-table-row-${index}`}
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
                                                <ListFilter className="size-4" />
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.name}</span>
                                                <span className="text-muted-foreground mt-0.5 block truncate text-xs">Kode: {row.code}</span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <Badge variant="secondary" className="max-w-full truncate rounded-sm">
                                            {categoryLabel(row.category)}
                                        </Badge>
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
                                            {row.deleted_at && canRestore && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8"
                                                    title={`Pulihkan ${row.name}`}
                                                    onClick={() => onRestore(row)}
                                                >
                                                    <RotateCcw className="size-4" />
                                                </Button>
                                            )}
                                            {!row.deleted_at && canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive size-8"
                                                    title={`Arsipkan ${row.name}`}
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
                                                    className="text-destructive hover:text-destructive size-8"
                                                    title={`Hapus permanen ${row.name}`}
                                                    onClick={() => onDelete(row)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!referenceData.data.length && (
                                <tr>
                                    <td colSpan={4} className="text-muted-foreground px-4 py-10 text-center">
                                        Belum ada reference data yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-muted-foreground text-sm">
                    Showing {referenceData.data.length} of {referenceData.total} reference data
                </div>
                <PaginationBar
                    meta={referenceData}
                    routeName="hr.hr-reference-data.index"
                    filters={{ search, category, status, archive }}
                    idPrefix="hr-reference-data"
                />
            </div>
        </div>
    );
}
