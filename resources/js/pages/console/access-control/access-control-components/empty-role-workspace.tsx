import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Plus } from 'lucide-react';

interface EmptyRoleWorkspaceProps {
    canCreateRole: boolean;
    onAddRole: () => void;
}

export function EmptyRoleWorkspace({ canCreateRole, onAddRole }: EmptyRoleWorkspaceProps) {
    return (
        <div className="grid gap-4 xl:grid-cols-[300px_1fr]">
            <Card data-dashboard-card>
                <CardContent className="space-y-4 p-4">
                    <div>
                        <h2 className="text-sm font-semibold">Role kosong</h2>
                        <p className="text-muted-foreground mt-1 text-xs">Belum ada role yang bisa dikelola.</p>
                    </div>

                    {canCreateRole ? (
                        <Button type="button" className="w-full" onClick={onAddRole}>
                            <Plus className="size-4" />
                            Add Role
                        </Button>
                    ) : (
                        <div className="text-muted-foreground rounded-lg border border-dashed p-3 text-xs">
                            Kamu tidak memiliki akses untuk membuat role baru.
                        </div>
                    )}
                </CardContent>
            </Card>

            <Card data-dashboard-card>
                <CardContent className="p-6 text-center">
                    <p className="text-sm font-medium">Role belum tersedia</p>
                    <p className="text-muted-foreground mt-1 text-xs">Tambahkan role terlebih dahulu sebelum mengatur permission.</p>
                </CardContent>
            </Card>
        </div>
    );
}
