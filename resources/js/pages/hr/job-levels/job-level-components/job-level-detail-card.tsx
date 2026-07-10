import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArchiveRestore, CalendarClock, Layers } from 'lucide-react';
import type { JobLevelRow } from '../types';

type Props = {
    jobLevel: JobLevelRow | null;
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

export function JobLevelDetailCard({ jobLevel }: Props) {
    if (!jobLevel) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <Layers className="size-4" />
                        </span>
                        Job Level Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm text-muted-foreground">
                    <p>Pilih salah satu job level pada tabel untuk melihat detailnya di panel ini.</p>
                    <p>Job level dipakai sebagai grade jabatan yang bisa menjadi dasar approval, benefit, dan payroll.</p>
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
                                <Layers className="size-4" />
                            </span>
                            {jobLevel.name}
                        </CardTitle>
                        <p className="mt-2 text-sm text-muted-foreground">Kode: {jobLevel.code}</p>
                    </div>
                    {jobLevel.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={jobLevel.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {jobLevel.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4 p-5">
                <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Deskripsi</p>
                    <p className="mt-1 text-sm leading-6">{jobLevel.description || 'Belum ada deskripsi untuk job level ini.'}</p>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <ArchiveRestore className="mt-0.5 size-4 text-muted-foreground" />
                        <div>
                            <p className="text-sm font-medium">Penggunaan data</p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Job level ini bersifat lintas departement dan disiapkan sebagai referensi untuk employee profile, approval, benefit, dan payroll.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="mt-0.5 size-4 text-muted-foreground" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {formatDate(jobLevel.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {formatDate(jobLevel.updated_at)}
                            </p>
                            {jobLevel.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {formatDate(jobLevel.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
