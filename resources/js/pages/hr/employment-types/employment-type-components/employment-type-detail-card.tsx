import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArchiveRestore, BriefcaseBusiness, CalendarClock } from 'lucide-react';
import type { EmploymentTypeRow } from '../types';

type Props = {
    employmentType: EmploymentTypeRow | null;
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

export function EmploymentTypeDetailCard({ employmentType }: Props) {
    if (!employmentType) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <BriefcaseBusiness className="size-4" />
                        </span>
                        Employment Type Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="space-y-3 text-sm text-muted-foreground">
                    <p>Pilih salah satu Employment Type pada tabel untuk melihat detailnya di panel ini.</p>
                    <p>Tipe kerja dipakai untuk kontrak employee, benefit eligibility, overtime, payroll, dan report HR.</p>
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
                                <BriefcaseBusiness className="size-4" />
                            </span>
                            {employmentType.name}
                        </CardTitle>
                        <p className="mt-2 text-sm text-muted-foreground">Kode: {employmentType.code}</p>
                    </div>
                    {employmentType.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={employmentType.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {employmentType.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>

            <CardContent className="space-y-4 p-5">
                <div>
                    <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">Deskripsi</p>
                    <p className="mt-1 text-sm leading-6">{employmentType.description || 'Belum ada deskripsi untuk Employment Type ini.'}</p>
                </div>

                <div className="grid gap-2 sm:grid-cols-2">
                    <Badge variant={employmentType.requires_contract_end_date ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        End date {employmentType.requires_contract_end_date ? 'Wajib' : 'Opsional'}
                    </Badge>
                    <Badge variant={employmentType.included_in_payroll ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        Payroll {employmentType.included_in_payroll ? 'Ya' : 'Tidak'}
                    </Badge>
                    <Badge variant={employmentType.eligible_for_benefits ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        Benefit {employmentType.eligible_for_benefits ? 'Ya' : 'Tidak'}
                    </Badge>
                    <Badge variant={employmentType.eligible_for_overtime ? 'default' : 'secondary'} className="justify-center rounded-sm py-1.5">
                        Overtime {employmentType.eligible_for_overtime ? 'Ya' : 'Tidak'}
                    </Badge>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <ArchiveRestore className="mt-0.5 size-4 text-muted-foreground" />
                        <div>
                            <p className="text-sm font-medium">Penggunaan data</p>
                            <p className="mt-1 text-xs leading-5 text-muted-foreground">
                                Tipe kerja ini menjadi referensi Employees untuk menentukan kebutuhan end date kontrak, eligibility benefit, overtime, dan payroll.
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="mt-0.5 size-4 text-muted-foreground" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {formatDate(employmentType.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {formatDate(employmentType.updated_at)}
                            </p>
                            {employmentType.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {formatDate(employmentType.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
