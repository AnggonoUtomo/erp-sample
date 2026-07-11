import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CalendarClock, GitBranch, Network } from 'lucide-react';
import type { OrganizationStructureRow } from '../types';

type Props = { organizationStructure: OrganizationStructureRow | null };
const label = (value: string) => value.replaceAll('-', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase());
const date = (value: string | null) =>
    value ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '-';

export function OrganizationStructureDetailCard({ organizationStructure }: Props) {
    if (!organizationStructure) {
        return (
            <Card data-dashboard-card>
                <CardHeader>
                    <CardTitle className="flex items-center gap-2 text-lg">
                        <span className="dashboard-icon icon-tone-sky flex size-9 items-center justify-center rounded-lg">
                            <Network className="size-4" />
                        </span>
                        Structure Preview
                    </CardTitle>
                </CardHeader>
                <CardContent className="text-muted-foreground space-y-3 text-sm">
                    <p>Pilih salah satu structure pada tabel untuk melihat detailnya.</p>
                    <p>Organization structure menjadi fondasi reporting line, supervisor relationship, dan approval lintas project.</p>
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
                                <Network className="size-4" />
                            </span>
                            {organizationStructure.name}
                        </CardTitle>
                        <p className="text-muted-foreground mt-2 text-sm">
                            Kode: {organizationStructure.code} | Type: {label(organizationStructure.node_type)}
                        </p>
                    </div>
                    {organizationStructure.deleted_at ? (
                        <Badge className="bg-amber-600 text-white">Arsip</Badge>
                    ) : (
                        <Badge className={organizationStructure.active ? 'bg-emerald-600 text-white' : 'bg-stone-600 text-white'}>
                            {organizationStructure.active ? 'Aktif' : 'Nonaktif'}
                        </Badge>
                    )}
                </div>
            </CardHeader>
            <CardContent className="space-y-4 p-5">
                <div>
                    <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">Deskripsi</p>
                    <p className="mt-1 text-sm leading-6">{organizationStructure.description || 'Belum ada deskripsi untuk structure ini.'}</p>
                </div>
                <div className="grid gap-2 sm:grid-cols-2">
                    <Info
                        label="Parent"
                        value={
                            organizationStructure.parent ? `${organizationStructure.parent.name} (${organizationStructure.parent.code})` : 'Root node'
                        }
                    />
                    <Info label="Child aktif" value={`${organizationStructure.active_children_count} child aktif`} />
                    <Info
                        label="Departement"
                        value={
                            organizationStructure.departement
                                ? `${organizationStructure.departement.name} (${organizationStructure.departement.code})`
                                : 'Tidak terkait'
                        }
                    />
                    <Info
                        label="Position"
                        value={
                            organizationStructure.position
                                ? `${organizationStructure.position.name} (${organizationStructure.position.code})`
                                : 'Tidak terkait'
                        }
                    />
                </div>
                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <GitBranch className="text-muted-foreground mt-0.5 size-4" />
                        <p className="text-muted-foreground text-xs leading-5">
                            Node ini akan dipakai sebagai sumber hierarchy employee, reporting line, dan approval Attendance/Payroll saat modul
                            pemakainya tersedia.
                        </p>
                    </div>
                </div>
                <div className="rounded-lg border p-3">
                    <div className="flex items-start gap-3">
                        <CalendarClock className="text-muted-foreground mt-0.5 size-4" />
                        <div className="space-y-1 text-sm">
                            <p>
                                <span className="text-muted-foreground">Dibuat:</span> {date(organizationStructure.created_at)}
                            </p>
                            <p>
                                <span className="text-muted-foreground">Diperbarui:</span> {date(organizationStructure.updated_at)}
                            </p>
                            {organizationStructure.deleted_at && (
                                <p>
                                    <span className="text-muted-foreground">Diarsipkan:</span> {date(organizationStructure.deleted_at)}
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

function Info({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg border p-3">
            <p className="text-muted-foreground text-xs">{label}</p>
            <p className="mt-1 text-sm font-medium">{value}</p>
        </div>
    );
}
