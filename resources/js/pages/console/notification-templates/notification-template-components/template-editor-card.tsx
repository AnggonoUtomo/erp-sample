import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import type { InertiaFormProps } from '@inertiajs/react';
import { Braces, Save } from 'lucide-react';
import { type FormEvent } from 'react';
import type { NotificationTemplate, TemplateForm } from '../types';

type Props = {
    selectedTemplate: NotificationTemplate | null;
    canUpdate: boolean;
    form: InertiaFormProps<TemplateForm>;
    onSubmit: (event: FormEvent) => void;
};

export function TemplateEditorCard({ selectedTemplate, canUpdate, form, onSubmit }: Props) {
    return (
        <Card data-dashboard-card className="min-w-0 overflow-hidden">
            <CardHeader className="border-b">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <CardTitle>{selectedTemplate?.name ?? 'Template'}</CardTitle>
                        <CardDescription>
                            {selectedTemplate?.updated_at ? `Terakhir diperbarui ${selectedTemplate.updated_at}.` : 'Atur subject dan isi pesan template.'}
                        </CardDescription>
                    </div>
                    {selectedTemplate && <Badge variant="outline">{selectedTemplate.key}</Badge>}
                </div>
            </CardHeader>
            <CardContent className="p-5 sm:p-6">
                {selectedTemplate ? (
                    <form onSubmit={onSubmit} className="space-y-6">
                        <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_260px]">
                            <div className="space-y-5">
                                <div className="space-y-2">
                                    <label htmlFor="subject" className="text-sm font-medium">
                                        Subject
                                    </label>
                                    <Input
                                        id="subject"
                                        value={form.data.subject}
                                        disabled={!canUpdate}
                                        onChange={(event) => form.setData('subject', event.target.value)}
                                        placeholder="Subject email"
                                    />
                                    <InputError message={form.errors.subject} />
                                </div>

                                <div className="space-y-2">
                                    <label htmlFor="body" className="text-sm font-medium">
                                        Body
                                    </label>
                                    <textarea
                                        id="body"
                                        value={form.data.body}
                                        disabled={!canUpdate}
                                        onChange={(event) => form.setData('body', event.target.value)}
                                        rows={14}
                                        className="border-input bg-background ring-offset-background placeholder:text-muted-foreground focus-visible:ring-ring min-h-72 w-full rounded-md border px-3 py-2 text-sm whitespace-pre-wrap shadow-sm transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Isi pesan notifikasi"
                                    />
                                    <InputError message={form.errors.body} />
                                </div>

                                <label className="flex items-center gap-3 rounded-md border p-3 text-sm">
                                    <Checkbox checked={form.data.active} disabled={!canUpdate} onCheckedChange={(checked) => form.setData('active', checked === true)} />
                                    <span>
                                        <span className="block font-medium">Template aktif</span>
                                        <span className="text-muted-foreground text-xs">Jika nonaktif, sistem tetap memakai fallback bawaan.</span>
                                    </span>
                                </label>
                                <InputError message={form.errors.active} />
                            </div>

                            <aside className="space-y-4">
                                <div className="rounded-lg border p-4">
                                    <div className="mb-3 flex items-center gap-2 text-sm font-medium">
                                        <Braces className="size-4" />
                                        Variable
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {selectedTemplate.variables.map((variable) => (
                                            <code key={variable} className="bg-muted text-muted-foreground rounded px-2 py-1 text-xs">
                                                {'{{ ' + variable + ' }}'}
                                            </code>
                                        ))}
                                    </div>
                                </div>
                                <div className="bg-muted/40 rounded-lg border p-4 text-xs leading-5">
                                    Gunakan variable dengan format kurung kurawal. Contoh: <code className="bg-background rounded px-1 py-0.5">{'{{ name }}'}</code>.
                                </div>
                            </aside>
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" disabled={!canUpdate || form.processing}>
                                <Save className="size-4" />
                                Simpan Template
                            </Button>
                        </div>
                    </form>
                ) : (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">Belum ada template.</div>
                )}
            </CardContent>
        </Card>
    );
}
