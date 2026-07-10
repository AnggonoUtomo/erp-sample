import { PaginationBar } from '@/components/pagination-bar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArchiveRestore, Edit3, Layers, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import type { JobLevelRow, Paginator } from '../types';

type Props = {
    jobLevels: Paginator<JobLevelRow>;
    search: string;
    status: string;
    archive: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    canRestore: boolean;
    canForceDelete: boolean;
    onSearchChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchiveChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (jobLevel: JobLevelRow) => void;
    onEdit: (jobLevel: JobLevelRow) => void;
    onDelete: (jobLevel: JobLevelRow) => void;
    onRestore: (jobLevel: JobLevelRow) => void;
};

export function JobLevelTable({
    jobLevels,
    search,
    status,
    archive,
    canCreate,
    canUpdate,
    canDelete,
    canRestore,
    canForceDelete,
    onSearchChange,
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
                        <h2 className="text-lg font-semibold tracking-tight">Job Level Directory</h2>
                        <p className="mt-0.5 text-sm text-muted-foreground">Cari, filter, dan kelola grade jabatan HR.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {jobLevels.total} total
                    </Badge>
                </div>

                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="job-level-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari job level"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="job-level-status-filter-trigger" className="h-10 w-full md:w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={archive} onValueChange={onArchiveChange}>
                        <SelectTrigger id="job-level-archive-filter-trigger" className="h-10 w-full md:w-[150px]">
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
                        Tambah Job Level
                    </Button>
                )}
            </div>

            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[540px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-left text-muted-foreground">
                            <tr>
                                <th className="w-[48%] px-2.5 py-2.5 font-semibold">Job Level</th>
                                <th className="w-[22%] px-2.5 py-2.5 font-semibold">Penggunaan</th>
                                <th className="w-[14%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[16%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {jobLevels.data.map((row, index) => (
                                <tr key={row.id} className="border-t transition hover:bg-muted/40">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`job-level-table-row-${index}`}
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
                                                <Layers className="size-4" />
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.name}</span>
                                                <span className="mt-0.5 block truncate text-xs text-muted-foreground">Kode: {row.code}</span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <div className="min-w-0">
                                            <div className="truncate font-medium">Lintas departement</div>
                                            <div className="truncate text-xs text-muted-foreground">Referensi employee dan payroll</div>
                                        </div>
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
                                                <Button type="button" variant="ghost" size="icon" className="size-8" title={`Edit ${row.name}`} onClick={() => onEdit(row)}>
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {row.deleted_at && canRestore && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" title={`Pulihkan ${row.name}`} onClick={() => onRestore(row)}>
                                                    <RotateCcw className="size-4" />
                                                </Button>
                                            )}
                                            {!row.deleted_at && canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="size-8 text-destructive hover:text-destructive"
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
                                                    className="size-8 text-destructive hover:text-destructive"
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
                            {!jobLevels.data.length && (
                                <tr>
                                    <td colSpan={4} className="px-4 py-10 text-center text-muted-foreground">
                                        Belum ada Job Level yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-sm text-muted-foreground">
                    Showing {jobLevels.data.length} of {jobLevels.total} job levels
                </div>
                <PaginationBar
                    meta={jobLevels}
                    routeName="hr.job-levels.index"
                    filters={{ search, status, archive }}
                    idPrefix="job-levels"
                />
            </div>
        </div>
    );
}
