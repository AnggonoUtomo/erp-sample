import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { BriefcaseBusiness, Building2 } from 'lucide-react';
import type { PositionRow } from '../types';

export function PositionDetailCard({ position }: { position: PositionRow | null }) {
    if (!position) {
        return (
            <Card data-dashboard-card className="overflow-hidden">
                <CardHeader className="bg-muted/20 border-b">
                    <CardTitle className="flex items-center gap-2 text-base">
                        <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-lg">
                            <BriefcaseBusiness className="size-4" />
                        </span>
                        Position Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="flex min-h-[380px] flex-col items-center justify-center gap-3 px-6 py-10 text-center">
                    <div className="dashboard-icon icon-tone-sky flex size-16 items-center justify-center rounded-lg">
                        <BriefcaseBusiness className="size-8" />
                    </div>
                    <div className="space-y-1">
                        <p className="font-medium">Pilih position dari tabel</p>
                        <p className="text-muted-foreground max-w-[260px] text-sm">
                            Detail jabatan, departement, status, dan deskripsi akan tampil di sini.
                        </p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card data-dashboard-card className="overflow-hidden">
            <CardHeader className="bg-muted/20 border-b">
                <CardTitle className="flex items-center gap-2 text-base">
                    <span className="dashboard-icon icon-tone-sky flex size-8 items-center justify-center rounded-lg">
                        <BriefcaseBusiness className="size-4" />
                    </span>
                    Position Preview
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-5 p-5">
                <div className="space-y-3 text-center">
                    <div className="bg-primary/10 text-primary mx-auto flex size-20 items-center justify-center rounded-xl">
                        <BriefcaseBusiness className="size-9" />
                    </div>
                    <div className="space-y-1">
                        <p className="text-xl font-semibold">{position.name}</p>
                        <div className="flex items-center justify-center gap-2">
                            <Badge variant="secondary" className="rounded-sm">
                                {position.code}
                            </Badge>
                            <Badge className={position.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                {position.active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                        </div>
                    </div>
                </div>

                <Separator />

                <div className="grid gap-3 text-sm">
                    <div className="flex items-center justify-between gap-3">
                        <span className="text-muted-foreground flex items-center gap-2">
                            <Building2 className="size-4" />
                            Departement
                        </span>
                        <span className="text-right font-medium">
                            {position.departement ? `${position.departement.name} (Kode: ${position.departement.code})` : 'Belum terhubung'}
                        </span>
                    </div>
                </div>

                <Separator />

                <div className="space-y-2">
                    <p className="text-sm font-medium">Deskripsi</p>
                    <p className="bg-muted/20 text-muted-foreground min-h-[96px] rounded-lg border p-3 text-sm leading-6">
                        {position.description || 'Belum ada deskripsi untuk position ini.'}
                    </p>
                </div>
            </CardContent>
        </Card>
    );
}
