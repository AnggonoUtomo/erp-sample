import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArchiveRestore, BadgeCheck, CalendarClock } from 'lucide-react';
import type { EmploymentStatusRow } from '../types';

type Props = {
    employmentStatus: EmploymentStatusRow | null;
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

export function EmploymentStatusDetailCard({ employmentStatus }: Props) {
    if (!employmentStatus) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <BadgeCheck className="size-4" />
                        </span>
                        Employment Status Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm text-muted-foreground">
                    <p>Pilih salah satu Employment Status pada tabel untuk melihat detailnya di panel ini.</p>
                    <p>Status kerja dipakai untuk lifecycle employee, attendance eligibility, payroll inclusion, dan report HR.</p>
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
                                <BadgeCheck className="size-4" />
                            </span>
                            {employmentStatus.name}
                        </CardTitle>
                        <p className="mt-2 text-sm text-muted-foreground">Kode: {employmentStatus.code}</p>
                    </div>
                    {employmentStatus.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={employmentStatus.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {employmentStatus.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4 p-5">
                <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Deskripsi</p>
                    <p className="mt-1 text-sm leading-6">{employmentStatus.description || 'Belum ada deskripsi untuk Employment Status ini.'}</p>
                </div>

                <div className="grid gap-2 sm:grid-cols-3">
                    <Badge variant={employmentStatus.requires_attendance ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        Attendance {employmentStatus.requires_attendance ? 'Ya' : 'Tidak'}
                    </Badge>
                    <Badge variant={employmentStatus.included_in_payroll ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        Payroll {employmentStatus.included_in_payroll ? 'Ya' : 'Tidak'}
                    </Badge>
                    <Badge variant={employmentStatus.is_final_status ? 'destructive' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        {employmentStatus.is_final_status ? 'Status akhir' : 'Status berjalan'}
                    </Badge>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <ArchiveRestore className="mt-0.5 size-4 text-muted-foreground" />
                        <div>
                            <p className="text-sm font-medium">Penggunaan data</p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Status kerja ini menjadi referensi Employees untuk menentukan apakah seseorang masih aktif, wajib attendance, masuk payroll, atau sudah berada pada status akhir.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="mt-0.5 size-4 text-muted-foreground" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {formatDate(employmentStatus.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {formatDate(employmentStatus.updated_at)}
                            </p>
                            {employmentStatus.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {formatDate(employmentStatus.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
