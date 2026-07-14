import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { usePermission } from '@/hooks/use-permission';
import AppLayout from '@/layouts/app-layout';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Archive, ArrowDown, ArrowUp, ListChecks, Plus, RotateCcw, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import type { TemplateForm, TemplateItemForm, TemplatePaginator } from './types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'HR', href: '/hr/dashboard' },
    { title: 'Onboarding Templates', href: '/hr/onboardings/templates' },
];

const emptyItem = (): TemplateItemForm => ({
    title: '',
    description: '',
    category: 'HR',
    required: true,
    due_offset_days: 0,
    default_assignee_role: '',
});

const emptyForm = (): TemplateForm => ({ code: '', name: '', description: '', active: true, items: [emptyItem()] });

export default function OnboardingTemplatesIndex({ templates, showArchived }: { templates: TemplatePaginator; showArchived: boolean }) {
    const { canAny } = usePermission();
    const canCreate = canAny(['onboardings.template-manage', 'onboardings.manage']);
    const canManageLifecycle = canCreate;
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
        form.post(route('hr.onboardings.templates.store'), {
            preserveScroll: true,
            onSuccess: () => form.setData(emptyForm()),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Onboarding Templates" />
            <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-6 p-4 sm:p-6">
                <div>
                    <div className="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h1 className="flex items-center gap-2 text-2xl font-semibold tracking-tight">
                                <ListChecks className="size-6" /> Onboarding Templates
                            </h1>
                            <p className="text-muted-foreground mt-1 text-sm">
                                Susun checklist standar. Urutan yang disimpan akan menjadi dasar snapshot onboarding.
                            </p>
                        </div>
                        <Button asChild variant="outline" size="sm">
                            <Link href={showArchived ? '/hr/onboardings/templates' : '/hr/onboardings/templates?archived=1'} preserveScroll>
                                <Archive className="mr-2 size-4" /> {showArchived ? 'Sembunyikan arsip' : 'Tampilkan arsip'}
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className={`grid gap-6 ${canCreate ? 'xl:grid-cols-[minmax(0,1fr)_minmax(360px,0.8fr)]' : ''}`}>
                    <div className="space-y-4">
                        {templates.data.length === 0 && (
                            <Card>
                                <CardContent className="text-muted-foreground p-8 text-center">Belum ada template onboarding.</CardContent>
                            </Card>
                        )}
                        {templates.data.map((template) => (
                            <Card key={template.id}>
                                <CardHeader className="pb-3">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <CardTitle className="text-base">{template.name}</CardTitle>
                                            <p className="text-muted-foreground mt-1 text-xs">{template.code}</p>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <Badge variant={template.archived ? 'outline' : template.active ? 'default' : 'secondary'}>
                                                {template.archived ? 'Diarsipkan' : template.active ? 'Aktif' : 'Nonaktif'}
                                            </Badge>
                                            {canManageLifecycle && (
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={template.archived ? `Restore ${template.name}` : `Arsipkan ${template.name}`}
                                                    onClick={() => {
                                                        if (template.archived) {
                                                            router.patch(
                                                                route('hr.onboardings.templates.restore', template.id),
                                                                {},
                                                                { preserveScroll: true },
                                                            );
                                                            return;
                                                        }
                                                        if (window.confirm(`Arsipkan template ${template.name}?`)) {
                                                            router.delete(route('hr.onboardings.templates.archive', template.id), {
                                                                preserveScroll: true,
                                                            });
                                                        }
                                                    }}
                                                >
                                                    {template.archived ? <RotateCcw className="size-4" /> : <Archive className="size-4" />}
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    {template.description && <p className="text-muted-foreground mb-3 text-sm">{template.description}</p>}
                                    <ol className="space-y-2">
                                        {template.items.map((item, index) => (
                                            <li key={item.id} className="bg-muted/50 flex gap-3 rounded-lg border p-3 text-sm">
                                                <span className="font-medium">{index + 1}.</span>
                                                <div className="min-w-0 flex-1">
                                                    <div className="font-medium">{item.title}</div>
                                                    <div className="text-muted-foreground mt-1 text-xs">
                                                        {item.category} · H{item.due_offset_days >= 0 ? '+' : ''}
                                                        {item.due_offset_days} · {item.required ? 'Wajib' : 'Opsional'}
                                                    </div>
                                                </div>
                                            </li>
                                        ))}
                                    </ol>
                                </CardContent>
                            </Card>
                        ))}
                        {templates.last_page > 1 && (
                            <div className="flex items-center justify-between gap-3">
                                <Button asChild={Boolean(templates.prev_page_url)} variant="outline" size="sm" disabled={!templates.prev_page_url}>
                                    {templates.prev_page_url ? (
                                        <Link href={templates.prev_page_url} preserveScroll>
                                            Previous
                                        </Link>
                                    ) : (
                                        <span>Previous</span>
                                    )}
                                </Button>
                                <span className="text-muted-foreground text-xs">
                                    Halaman {templates.current_page} dari {templates.last_page}
                                </span>
                                <Button asChild={Boolean(templates.next_page_url)} variant="outline" size="sm" disabled={!templates.next_page_url}>
                                    {templates.next_page_url ? (
                                        <Link href={templates.next_page_url} preserveScroll>
                                            Next
                                        </Link>
                                    ) : (
                                        <span>Next</span>
                                    )}
                                </Button>
                            </div>
                        )}
                    </div>

                    {canCreate && (
                        <Card className="h-fit xl:sticky xl:top-6">
                            <CardHeader>
                                <CardTitle className="text-lg">Template baru</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-5">
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <Field label="Kode" error={form.errors.code}>
                                            <Input
                                                value={form.data.code}
                                                onChange={(e) => form.setData('code', e.target.value)}
                                                placeholder="NEW-HIRE"
                                            />
                                        </Field>
                                        <Field label="Nama" error={form.errors.name}>
                                            <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                                        </Field>
                                    </div>
                                    <Field label="Deskripsi" error={form.errors.description}>
                                        <Textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                                    </Field>
                                    <label className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={form.data.active}
                                            onCheckedChange={(checked) => form.setData('active', checked === true)}
                                        />{' '}
                                        Aktif
                                    </label>

                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <Label>Ordered items</Label>
                                            <Button
                                                type="button"
                                                variant="outline"
                                                size="sm"
                                                onClick={() => form.setData('items', [...form.data.items, emptyItem()])}
                                            >
                                                <Plus className="mr-1 size-4" /> Item
                                            </Button>
                                        </div>
                                        {form.data.items.map((item, index) => (
                                            <div key={index} className="space-y-3 rounded-lg border p-3">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-sm font-medium">Item {index + 1}</span>
                                                    <div className="flex gap-1">
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            disabled={index === 0}
                                                            onClick={() => moveItem(index, -1)}
                                                        >
                                                            <ArrowUp className="size-4" />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            disabled={index === form.data.items.length - 1}
                                                            onClick={() => moveItem(index, 1)}
                                                        >
                                                            <ArrowDown className="size-4" />
                                                        </Button>
                                                        <Button
                                                            type="button"
                                                            size="icon"
                                                            variant="ghost"
                                                            disabled={form.data.items.length === 1}
                                                            onClick={() =>
                                                                form.setData(
                                                                    'items',
                                                                    form.data.items.filter((_, i) => i !== index),
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </div>
                                                </div>
                                                <Input
                                                    value={item.title}
                                                    onChange={(e) => updateItem(index, 'title', e.target.value)}
                                                    placeholder="Judul task"
                                                />
                                                <div className="grid grid-cols-2 gap-3">
                                                    <Input
                                                        value={item.category}
                                                        onChange={(e) => updateItem(index, 'category', e.target.value)}
                                                        placeholder="Kategori"
                                                    />
                                                    <Input
                                                        type="number"
                                                        min={-365}
                                                        max={365}
                                                        value={item.due_offset_days}
                                                        onChange={(e) => updateItem(index, 'due_offset_days', Number(e.target.value))}
                                                        aria-label="Due offset hari"
                                                    />
                                                </div>
                                                <Input
                                                    value={item.default_assignee_role}
                                                    onChange={(e) => updateItem(index, 'default_assignee_role', e.target.value)}
                                                    placeholder="Default role (opsional)"
                                                />
                                                <Textarea
                                                    value={item.description}
                                                    onChange={(e) => updateItem(index, 'description', e.target.value)}
                                                    placeholder="Deskripsi item (opsional)"
                                                />
                                                <label className="flex items-center gap-2 text-sm">
                                                    <Checkbox
                                                        checked={item.required}
                                                        onCheckedChange={(checked) => updateItem(index, 'required', checked === true)}
                                                    />{' '}
                                                    Wajib
                                                </label>
                                                {form.errors[`items.${index}.title`] && (
                                                    <p className="text-destructive text-xs">{form.errors[`items.${index}.title`]}</p>
                                                )}
                                            </div>
                                        ))}
                                    </div>
                                    <Button type="submit" disabled={form.processing} className="w-full">
                                        {form.processing ? 'Menyimpan…' : 'Simpan template'}
                                    </Button>
                                </form>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: React.ReactNode }) {
    return (
        <div className="space-y-2">
            <Label>{label}</Label>
            {children}
            {error && <p className="text-destructive text-xs">{error}</p>}
        </div>
    );
}
