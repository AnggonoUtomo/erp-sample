import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Mail, MapPin, Phone, UserRound } from 'lucide-react';
import type { EmployeeRow } from '../types';

type Props = {
    employee: EmployeeRow | null;
};

function value(value: string | null | undefined) {
    return value || '-';
}

export function EmployeeDetailCard({ employee }: Props) {
    if (!employee) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle>Employee Preview</CardTitle>
                </CardHeader>
                <CardContent className="text-muted-foreground text-sm">
                    Pilih employee pada tabel untuk melihat detail work profile, avatar, dan relasi user.
                </CardContent>
            </Card>
        );
    }

    return (
        <Card data-dashboard-card>
            <CardHeader className="border-b">
                <div className="flex items-start gap-3">
                    <Avatar className="size-16 rounded-xl">
                        <AvatarImage src={employee.avatar ?? undefined} alt={employee.display_name} />
                        <AvatarFallback className="rounded-xl text-lg">{employee.display_name.slice(0, 2).toUpperCase()}</AvatarFallback>
                    </Avatar>
                    <div className="min-w-0">
                        <CardTitle className="truncate text-xl">{employee.display_name}</CardTitle>
                        <p className="text-muted-foreground mt-1 text-sm">{employee.employee_number}</p>
                        <div className="mt-2 flex flex-wrap gap-2">
                            <Badge className={employee.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                                {employee.active ? 'Aktif' : 'Nonaktif'}
                            </Badge>
                            {employee.deleted_at && <Badge className="bg-amber-600 text-white">Arsip</Badge>}
                        </div>
                    </div>
                </div>
            </CardHeader>
            <CardContent className="space-y-5 p-5">
                <div className="grid gap-3 text-sm">
                    <div className="flex items-center gap-2">
                        <Mail className="text-muted-foreground size-4" />
                        <span>{value(employee.work_email ?? employee.personal_email)}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <Phone className="text-muted-foreground size-4" />
                        <span>{value(employee.phone)}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <MapPin className="text-muted-foreground size-4" />
                        <span>{employee.work_location?.name ?? 'Lokasi belum dipilih'}</span>
                    </div>
                    <div className="flex items-center gap-2">
                        <UserRound className="text-muted-foreground size-4" />
                        <span>{employee.user ? `${employee.user.name} (${employee.user.email})` : 'Belum terhubung user login'}</span>
                    </div>
                </div>

                <div className="grid gap-3 rounded-lg border p-3 text-sm">
                    <div>
                        <p className="text-muted-foreground text-xs">Departement</p>
                        <p className="font-medium">{employee.departement?.name ?? '-'}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Position / Job Level</p>
                        <p className="font-medium">
                            {employee.position?.name ?? '-'} / {employee.job_level?.name ?? '-'}
                        </p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Employment</p>
                        <p className="font-medium">
                            {employee.employment_status?.name ?? '-'} / {employee.employment_type?.name ?? '-'}
                        </p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Supervisor</p>
                        <p className="font-medium">{employee.supervisor?.display_name ?? '-'}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Join - End</p>
                        <p className="font-medium">
                            {value(employee.hired_at)} - {value(employee.ended_at)}
                        </p>
                    </div>
                </div>

                <div className="grid gap-3 rounded-lg border p-3 text-sm">
                    <div>
                        <p className="text-muted-foreground text-xs">Birth</p>
                        <p className="font-medium">
                            {value(employee.place_of_birth)} / {value(employee.date_of_birth)}
                        </p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Address</p>
                        <p className="font-medium">{value(employee.address)}</p>
                    </div>
                    <div>
                        <p className="text-muted-foreground text-xs">Emergency Contact</p>
                        <p className="font-medium">
                            {value(employee.emergency_contact_name)} · {value(employee.emergency_contact_phone)} ·{' '}
                            {value(employee.emergency_contact_relation)}
                        </p>
                    </div>
                </div>

                {employee.notes && (
                    <div className="rounded-lg border p-3 text-sm">
                        <p className="text-muted-foreground text-xs">Catatan</p>
                        <p className="mt-1 leading-6">{employee.notes}</p>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
