import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArchiveRestore, CalendarClock, ListFilter } from 'lucide-react';
import type { ReferenceDataRow } from '../types';

type Props = {
    referenceData: ReferenceDataRow | null;
};

function formatDate(value: string | null) {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function categoryLabel(value: string) {
    return value.replaceAll('-', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
}

export function HRReferenceDataDetailCard({ referenceData }: Props) {
    if (!referenceData) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <ListFilter className="size-4" />
                        </span>
                        Reference Data Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm text-muted-foreground">
                    <p>Pilih salah satu reference data pada tabel untuk melihat detailnya di panel ini.</p>
                    <p>Reference data dipakai sebagai sumber pilihan dropdown untuk employee profile, dokumen, report, dan integrasi HR.</p>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card data-dashboard-card>
            <CardHeader className="space-y-4 border-b">
                <div className="flex items-start justify-between gap-3">
                    <div>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                                <ListFilter className="size-4" />
                            </span>
                            {referenceData.name}
                        </CardTitle>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Kategori: {categoryLabel(referenceData.category)} | Kode: {referenceData.code}
                        </p>
                    </div>
                    {referenceData.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={referenceData.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {referenceData.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4 p-5">
                <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Deskripsi</p>
                    <p className="mt-1 text-sm leading-6">{referenceData.description || 'Belum ada deskripsi untuk reference data ini.'}</p>
                </div>

                <div className="grid gap-2 sm:grid-cols-2">
                    <div className="rounded-lg border p-3">
                        <p className="text-xs text-muted-foreground">Kategori</p>
                        <p className="mt-1 text-sm font-medium">{categoryLabel(referenceData.category)}</p>
                    </div>
                    <div className="rounded-lg border p-3">
                        <p className="text-xs text-muted-foreground">Kode</p>
                        <p className="mt-1 text-sm font-medium">{referenceData.code}</p>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <ArchiveRestore className="mt-0.5 size-4 text-muted-foreground" />
                        <div>
                            <p className="text-sm font-medium">Penggunaan data</p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Reference data ini menjadi pilihan master untuk Employees, report HR, dan integrasi project lain melalui contract/event.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="mt-0.5 size-4 text-muted-foreground" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {formatDate(referenceData.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {formatDate(referenceData.updated_at)}
                            </p>
                            {referenceData.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {formatDate(referenceData.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
