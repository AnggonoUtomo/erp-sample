import { Card, CardContent } from '@/components/ui/card';
import type { BackupOverview } from '../types';

export function BackupSummaryCards({ overview }: { overview: BackupOverview }) {
    return (
        <div className="grid gap-4 md:grid-cols-3">
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">System Settings</p>
                    <p className="mt-2 text-2xl font-semibold">{overview.system_settings}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Encrypted Settings</p>
                    <p className="mt-2 text-2xl font-semibold">{overview.encrypted_settings}</p>
                </CardContent>
            </Card>
            <Card data-dashboard-card>
                <CardContent className="p-4">
                    <p className="text-muted-foreground text-xs">Notification Templates</p>
                    <p className="mt-2 text-2xl font-semibold">{overview.notification_templates}</p>
                </CardContent>
            </Card>
        </div>
    );
}
