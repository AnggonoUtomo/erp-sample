import { PaginationBar } from '@/components/pagination-bar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ArchiveRestore, Edit3, Network, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import type { OrganizationStructureRow, Paginator, SimpleOption } from '../types';

type Props = {
    organizationStructures: Paginator<OrganizationStructureRow>;
    nodeTypeOptions: SimpleOption[];
    search: string;
    nodeType: string;
    status: string;
    archive: string;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    canRestore: boolean;
    canForceDelete: boolean;
    onSearchChange: (value: string) => void;
    onNodeTypeChange: (value: string) => void;
    onStatusChange: (value: string) => void;
    onArchiveChange: (value: string) => void;
    onAdd: () => void;
    onSelect: (row: OrganizationStructureRow) => void;
    onEdit: (row: OrganizationStructureRow) => void;
    onDelete: (row: OrganizationStructureRow) => void;
    onRestore: (row: OrganizationStructureRow) => void;
};
const label = (value: string) => value.replaceAll('-', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());

export function OrganizationStructureTable({
    organizationStructures,
    nodeTypeOptions,
    search,
    nodeType,
    status,
    archive,
    canCreate,
    canUpdate,
    canDelete,
    canRestore,
    canForceDelete,
    onSearchChange,
    onNodeTypeChange,
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
                        <h2 className="text-lg font-semibold tracking-tight">Organization Structure Directory</h2>
                        <p className="text-muted-foreground mt-0.5 text-sm">Cari, filter, dan kelola hierarchy organisasi HR.</p>
                    </div>
                    <Badge variant="outline" className="shrink-0 rounded-sm">
                        {organizationStructures.total} total
                    </Badge>
                </div>
                <div className="flex min-w-0 flex-1 flex-col gap-2 md:flex-row md:flex-wrap">
                    <div className="relative md:w-[220px]">
                        <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
                        <Input
                            id="organization-structure-search-input"
                            value={search}
                            onChange={(event) => onSearchChange(event.target.value)}
                            placeholder="Cari Structure"
                            className="h-10 w-full pl-9"
                        />
                    </div>
                    <Select value={nodeType} onValueChange={onNodeTypeChange}>
                        <SelectTrigger id="organization-structure-node-type-filter-trigger" className="h-10 w-full md:w-[150px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Node type</SelectItem>
                            {nodeTypeOptions.map((option) => (
                                <SelectItem key={String(option.value)} value={String(option.value)}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Select value={status} onValueChange={onStatusChange}>
                        <SelectTrigger id="organization-structure-status-filter-trigger" className="h-10 w-full md:w-[140px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Status</SelectItem>
                            <SelectItem value="active">Aktif</SelectItem>
                            <SelectItem value="inactive">Nonaktif</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={archive} onValueChange={onArchiveChange}>
                        <SelectTrigger id="organization-structure-archive-filter-trigger" className="h-10 w-full md:w-[150px]">
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
                        Tambah Structure
                    </Button>
                )}
            </div>
            <div className="w-full min-w-0 overflow-hidden rounded-md border">
                <div className="w-full overflow-x-auto">
                    <table className="w-full min-w-[620px] table-fixed text-sm">
                        <thead className="bg-muted/60 text-muted-foreground text-left">
                            <tr>
                                <th className="w-[38%] px-2.5 py-2.5 font-semibold">Structure</th>
                                <th className="w-[26%] px-2.5 py-2.5 font-semibold">Relasi</th>
                                <th className="w-[16%] px-2.5 py-2.5 font-semibold">Status</th>
                                <th className="w-[20%] px-2.5 py-2.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {organizationStructures.data.map((row, index) => (
                                <tr key={row.id} className="hover:bg-muted/40 border-t transition">
                                    <td className="px-2.5 py-2.5">
                                        <button
                                            id={`organization-structure-table-row-${index}`}
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
                                                <Network className="size-4" />
                                            </span>
                                            <span className="min-w-0">
                                                <span className="block truncate font-medium">{row.name}</span>
                                                <span className="text-muted-foreground mt-0.5 block truncate text-xs">
                                                    Kode: {row.code} | {label(row.node_type)}
                                                </span>
                                            </span>
                                        </button>
                                    </td>
                                    <td className="px-2.5 py-2.5">
                                        <div className="text-muted-foreground space-y-1 text-xs">
                                            <p className="truncate">Parent: {row.parent ? `${row.parent.name} (${row.parent.code})` : 'Root'}</p>
                                            <p className="truncate">
                                                Dept: {row.departement ? `${row.departement.name} (${row.departement.code})` : '-'}
                                            </p>
                                            <p>{row.active_children_count} child aktif</p>
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
                                                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onEdit(row)}>
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {row.deleted_at && canRestore && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onRestore(row)}>
                                                    <RotateCcw className="size-4" />
                                                </Button>
                                            )}
                                            {!row.deleted_at && canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive size-8"
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
                                                    onClick={() => onDelete(row)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {!organizationStructures.data.length && (
                                <tr>
                                    <td colSpan={4} className="text-muted-foreground px-4 py-10 text-center">
                                        Belum ada organization structure yang sesuai filter.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="text-muted-foreground text-sm">
                    Showing {organizationStructures.data.length} of {organizationStructures.total} structures
                </div>
                <PaginationBar
                    meta={organizationStructures}
                    routeName="hr.organization-structures.index"
                    filters={{ search, node_type: nodeType, status, archive }}
                    idPrefix="organization-structure"
                />
            </div>
        </div>
    );
}
