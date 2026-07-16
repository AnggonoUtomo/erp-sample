import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import type { FormEvent, ReactNode } from 'react';
import type { TemplateForm, TemplateItemForm } from '../types';
import { TemplateItemEditor } from './template-item-editor';

const emptyItem = (): TemplateItemForm => ({
    title: '',
    description: '',
    category: 'HR',
    required: true,
    due_offset_days: 0,
    default_assignee_role: '',
});

const emptyForm = (): TemplateForm => ({
    code: '',
    name: '',
    description: '',
    active: true,
    items: [emptyItem()],
});

export function OffboardingTemplateForm() {
    const form = useForm<TemplateForm>(emptyForm());

    const updateItem = <K extends keyof TemplateItemForm>(index: number, field: K, value: TemplateItemForm[K]) => {
        form.setData(
            'items',
            form.data.items.map((item, itemIndex) => (itemIndex === index ? { ...item, [field]: value } : item)),
        );
    };

    const moveItem = (index: number, direction: -1 | 1) => {
        const target = index + direction;
        if (target < 0 || target >= form.data.items.length) return;

        const items = [...form.data.items];
        [items[index], items[target]] = [items[target], items[index]];
        form.setData('items', items);
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('hr.offboardings.templates.store'), {
            preserveScroll: true,
            onSuccess: () => form.setData(emptyForm()),
        });
    };

    return (
        <Card className="h-fit xl:sticky xl:top-6">
            <CardHeader>
                <CardTitle className="text-lg">Template baru</CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={submit} className="space-y-5">
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field id="template-code" label="Kode" error={form.errors.code}>
                            <Input
                                id="template-code"
                                value={form.data.code}
                                onChange={(event) => form.setData('code', event.target.value)}
                                placeholder="STANDARD-EXIT"
                            />
                        </Field>
                        <Field id="template-name" label="Nama" error={form.errors.name}>
                            <Input
                                id="template-name"
                                value={form.data.name}
                                onChange={(event) => form.setData('name', event.target.value)}
                                placeholder="Standard Exit"
                            />
                        </Field>
                    </div>

                    <Field id="template-description" label="Deskripsi" error={form.errors.description}>
                        <Textarea
                            id="template-description"
                            value={form.data.description}
                            onChange={(event) => form.setData('description', event.target.value)}
                            placeholder="Tujuan dan cakupan checklist."
                        />
                    </Field>

                    <label className="flex items-center gap-2 text-sm">
                        <Checkbox checked={form.data.active} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                        Aktif untuk offboarding baru
                    </label>

                    <div className="space-y-3">
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <Label>Ordered items</Label>
                                <p className="text-muted-foreground mt-1 text-xs">Offset negatif berarti sebelum exit date.</p>
                            </div>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => form.setData('items', [...form.data.items, emptyItem()])}
                            >
                                <Plus className="mr-1 size-4" aria-hidden="true" /> Item
                            </Button>
                        </div>

                        {form.data.items.map((item, index) => (
                            <TemplateItemEditor
                                key={index}
                                item={item}
                                index={index}
                                total={form.data.items.length}
                                errors={form.errors}
                                updateItem={updateItem}
                                moveItem={moveItem}
                                removeItem={() =>
                                    form.setData(
                                        'items',
                                        form.data.items.filter((_, itemIndex) => itemIndex !== index),
                                    )
                                }
                            />
                        ))}
                    </div>

                    <Button type="submit" disabled={form.processing} className="w-full">
                        {form.processing ? 'Menyimpan…' : 'Simpan template'}
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}

function Field({ id, label, error, children }: { id: string; label: string; error?: string; children: ReactNode }) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            {children}
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}
