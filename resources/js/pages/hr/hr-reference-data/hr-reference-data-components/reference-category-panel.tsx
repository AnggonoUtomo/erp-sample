import { FieldInfoLabel } from '@/components/field-info-label';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { ChevronDown, Edit3, Layers3, Plus, Trash2, X } from 'lucide-react';
import type { FormEvent } from 'react';
import type { ReferenceCategoryForm, ReferenceCategoryRow } from '../types';

type Props = {
    categories: ReferenceCategoryRow[];
    form: InertiaFormProps<ReferenceCategoryForm>;
    editing: ReferenceCategoryRow | null;
    canCreate: boolean;
    canUpdate: boolean;
    canDelete: boolean;
    onSubmit: (event: FormEvent) => void;
    onEdit: (category: ReferenceCategoryRow) => void;
    onDelete: (category: ReferenceCategoryRow) => void;
    onCancel: () => void;
};

export function ReferenceCategoryPanel({ categories, form, editing, canCreate, canUpdate, canDelete, onSubmit, onEdit, onDelete, onCancel }: Props) {
    return (
        <Card data-dashboard-card>
            <Collapsible defaultOpen={false}>
                <CollapsibleTrigger className="group w-full text-left">
                    <CardHeader className="border-b">
                        <div className="flex items-start justify-between gap-3">
                            <div>
                                <CardTitle className="flex items-center gap-2 text-lg">
                                    <span className="dashboard-icon icon-tone-violet flex size-9 items-center justify-center rounded-lg">
                                        <Layers3 className="size-4" />
                                    </span>
                                    Kategori Reference
                                </CardTitle>
                                <p className="text-muted-foreground mt-2 text-sm">
                                    {categories.length} kategori terdaftar. Klik untuk mengelola kategori dropdown reference data.
                                </p>
                            </div>
                            <ChevronDown className="text-muted-foreground mt-2 size-4 shrink-0 transition-transform group-data-[state=open]:rotate-180" />
                        </div>
                    </CardHeader>
                </CollapsibleTrigger>
                <CollapsibleContent>
                    <CardContent className="space-y-4 p-5">
                        <form className="space-y-3" onSubmit={onSubmit}>
                            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                <div className="space-y-2">
                                    <FieldInfoLabel
                                        htmlFor="category_name"
                                        required
                                        description="Nama kategori yang tampil di dropdown reference data."
                                    >
                                        Nama kategori
                                    </FieldInfoLabel>
                                    <Input
                                        id="category_name"
                                        value={form.data.name}
                                        onChange={(event) => form.setData('name', event.target.value)}
                                        placeholder="Employment Document Type"
                                    />
                                    {form.errors.name && <p className="text-destructive text-sm">{form.errors.name}</p>}
                                </div>
                                <div className="space-y-2">
                                    <FieldInfoLabel
                                        htmlFor="category_code"
                                        required
                                        description="Kode kategori yang disimpan di database, misalnya gender, bank, atau blood-type."
                                    >
                                        Code
                                    </FieldInfoLabel>
                                    <Input
                                        id="category_code"
                                        value={form.data.code}
                                        onChange={(event) => form.setData('code', event.target.value)}
                                        placeholder="employment-document-type"
                                    />
                                    {form.errors.code && <p className="text-destructive text-sm">{form.errors.code}</p>}
                                </div>
                            </div>
                            <div className="space-y-2">
                                <FieldInfoLabel htmlFor="category_description" description="Keterangan penggunaan kategori.">
                                    Deskripsi
                                </FieldInfoLabel>
                                <textarea
                                    id="category_description"
                                    value={form.data.description}
                                    onChange={(event) => form.setData('description', event.target.value)}
                                    rows={2}
                                    className="border-input bg-background placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 min-h-16 w-full resize-y rounded-md border px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px]"
                                />
                            </div>
                            <label className="flex items-start gap-3 rounded-lg border p-3 text-sm">
                                <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                                <span>
                                    <span className="block font-medium">Kategori aktif</span>
                                    <span className="text-muted-foreground mt-1 block text-xs leading-5">
                                        Kategori aktif muncul di dropdown add/edit reference data.
                                    </span>
                                </span>
                            </label>
                            <div className="flex gap-2">
                                {(editing ? canUpdate : canCreate) && (
                                    <Button type="submit" disabled={form.processing} className="flex-1">
                                        <Plus className="size-4" />
                                        {form.processing ? 'Menyimpan...' : editing ? 'Simpan Kategori' : 'Tambah Kategori'}
                                    </Button>
                                )}
                                {editing && (
                                    <Button type="button" variant="outline" onClick={onCancel}>
                                        <X className="size-4" />
                                        Batal
                                    </Button>
                                )}
                            </div>
                        </form>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between gap-3">
                                <p className="text-sm font-medium">Daftar kategori</p>
                                <Badge variant="secondary" className="rounded-sm">
                                    {categories.length} item
                                </Badge>
                            </div>

                            <div className="max-h-80 space-y-2 overflow-y-auto pr-1">
                                {categories.map((category) => (
                                    <div key={category.id} className="flex items-start justify-between gap-3 rounded-lg border p-3">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <p className="truncate text-sm font-medium">{category.name}</p>
                                                <Badge variant="secondary" className="rounded-sm">
                                                    {category.code}
                                                </Badge>
                                                <Badge className={category.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                                    {category.active ? 'Aktif' : 'Nonaktif'}
                                                </Badge>
                                            </div>
                                            <p className="text-muted-foreground mt-1 text-xs">
                                                {category.items_count} reference data memakai kategori ini
                                            </p>
                                        </div>
                                        <div className="flex shrink-0 gap-1">
                                            {canUpdate && (
                                                <Button type="button" variant="ghost" size="icon" className="size-8" onClick={() => onEdit(category)}>
                                                    <Edit3 className="size-4" />
                                                </Button>
                                            )}
                                            {canDelete && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    className="text-destructive hover:text-destructive size-8"
                                                    disabled={category.items_count > 0}
                                                    title={
                                                        category.items_count > 0
                                                            ? 'Kategori sedang dipakai dan tidak bisa dihapus'
                                                            : `Hapus ${category.name}`
                                                    }
                                                    onClick={() => onDelete(category)}
                                                >
                                                    <Trash2 className="size-4" />
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </CollapsibleContent>
            </Collapsible>
        </Card>
    );
}
