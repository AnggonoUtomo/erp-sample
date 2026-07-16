import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { ArrowDown, ArrowUp, Trash2 } from 'lucide-react';
import type { ReactNode } from 'react';
import type { TemplateItemForm } from '../types';

type ItemEditorProps = {
    item: TemplateItemForm;
    index: number;
    total: number;
    errors: Partial<Record<string, string>>;
    updateItem: <K extends keyof TemplateItemForm>(index: number, field: K, value: TemplateItemForm[K]) => void;
    moveItem: (index: number, direction: -1 | 1) => void;
    removeItem: () => void;
};

export function TemplateItemEditor({ item, index, total, errors, updateItem, moveItem, removeItem }: ItemEditorProps) {
    const prefix = `items.${index}`;

    return (
        <fieldset className="space-y-3 rounded-md border p-3">
            <legend className="sr-only">Item checklist {index + 1}</legend>
            <div className="flex items-center justify-between gap-2">
                <span className="text-sm font-medium">Item {index + 1}</span>
                <div className="flex gap-1">
                    <IconButton label={`Naikkan item ${index + 1}`} disabled={index === 0} onClick={() => moveItem(index, -1)}>
                        <ArrowUp className="size-4" />
                    </IconButton>
                    <IconButton label={`Turunkan item ${index + 1}`} disabled={index === total - 1} onClick={() => moveItem(index, 1)}>
                        <ArrowDown className="size-4" />
                    </IconButton>
                    <IconButton label={`Hapus item ${index + 1}`} disabled={total === 1} onClick={removeItem}>
                        <Trash2 className="size-4" />
                    </IconButton>
                </div>
            </div>

            <Input
                value={item.title}
                onChange={(event) => updateItem(index, 'title', event.target.value)}
                placeholder="Judul task"
                aria-label={`Judul item ${index + 1}`}
                aria-invalid={Boolean(errors[`${prefix}.title`])}
            />
            {errors[`${prefix}.title`] && <p className="text-destructive text-xs">{errors[`${prefix}.title`]}</p>}

            <div className="grid gap-3 sm:grid-cols-2">
                <Input
                    value={item.category}
                    onChange={(event) => updateItem(index, 'category', event.target.value)}
                    placeholder="Kategori"
                    aria-label={`Kategori item ${index + 1}`}
                />
                <Input
                    type="number"
                    min={-365}
                    max={365}
                    value={item.due_offset_days}
                    onChange={(event) => updateItem(index, 'due_offset_days', Number(event.target.value))}
                    aria-label={`Offset hari item ${index + 1}`}
                />
            </div>

            <Input
                value={item.default_assignee_role}
                onChange={(event) => updateItem(index, 'default_assignee_role', event.target.value)}
                placeholder="Default role (opsional)"
                aria-label={`Default role item ${index + 1}`}
            />
            <Textarea
                value={item.description}
                onChange={(event) => updateItem(index, 'description', event.target.value)}
                placeholder="Deskripsi item (opsional)"
                aria-label={`Deskripsi item ${index + 1}`}
            />
            <label className="flex items-center gap-2 text-sm">
                <Checkbox checked={item.required} onCheckedChange={(checked) => updateItem(index, 'required', checked === true)} />
                Wajib
            </label>
        </fieldset>
    );
}

function IconButton({ label, disabled, onClick, children }: { label: string; disabled: boolean; onClick: () => void; children: ReactNode }) {
    return (
        <Button type="button" size="icon" variant="ghost" aria-label={label} disabled={disabled} onClick={onClick}>
            {children}
        </Button>
    );
}
