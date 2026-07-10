import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Building2, GitBranch } from 'lucide-react';
import type { DepartementRow } from '../types';

export function DepartementDetailCard({ departement }: { departement: DepartementRow | null }) {
    if (!departement) {
        return (
            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="border-b bg-muted/20">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-emerald flex size-8 items-center justify-center rounded-lg">
                            <Building2 className="size-4" />
                        </span>
                        Departement Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="flex min-h-[380px] flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                    <div className="dashboard-icon icon-tone-emerald flex size-16 items-center justify-center rounded-lg">
                        <Building2 className="size-8" />
                    </div>
                    <div className="space-y-1">
                        <p className="font-medium">Pilih departement dari tabel</p>
                        <p className="max-w-[260px] text-sm text-muted-foreground">Detail hierarchy, status, dan deskripsi unit organisasi akan tampil di sini.</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="border-b bg-muted/20">
                <CardTitle className="flex items-center gap-2 text-base">
                    <span className="dashboard-icon icon-tone-emerald flex size-8 items-center justify-center rounded-lg">
                        <Building2 className="size-4" />
                    </span>
                    Departement Preview
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5 p-5">
                <div className="space-y-3 text-center">
                    <div className="mx-auto flex size-20 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <Building2 className="size-9" />
                    </div>
                    <div className="space-y-1">
                        <p className="text-xl font-semibold">{departement.name}</p>
                        <div className="flex items-center justify-center gap-2">
                            <Badge variant="secondary" className="rounded-sm">
                                {departement.code}
                            </Badge>
                            <Badge className={departement.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                {departement.active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                        </div>
                    </div>
                </div>

                <Separator />

                <div className="grid gap-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <span className="flex items-center gap-2 text-muted-foreground">
                            <GitBranch className="size-4" />
                            Parent
                        </span>
                        <span className="text-right font-medium">
                            {departement.parent ? `${departement.parent.name} (Kode: ${departement.parent.code})` : 'Root departement'}
                        </span>
                    </div>
                    <div className="flex items-start justify-between gap-3">
                        <span className="flex items-center gap-2 text-muted-foreground">
                            <Building2 className="size-4" />
                            Sub-departement
                        </span>
                        <span className="max-w-[180px] text-right font-medium">
                            {departement.children_count > 0
                                ? `Memiliki ${departement.children_count} sub-departement`
                                : 'Belum memiliki sub-departement'}
                        </span>
                    </div>
                </div>

                <Separator />

                <div className="space-y-2">
                    <p className="text-sm font-medium">Deskripsi</p>
                    <p className="min-h-[96px] rounded-lg border bg-muted/20 p-3 text-sm leading-6 text-muted-foreground">
                        {departement.description || 'Belum ada deskripsi untuk departement ini.'}
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}
