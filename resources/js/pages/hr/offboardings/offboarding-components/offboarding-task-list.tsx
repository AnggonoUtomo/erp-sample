import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { CheckCircle2, ClipboardList, Clock3 } from 'lucide-react';
import type { IdName, OffboardingTaskRow } from '../types';
import { offboardingTaskStatusLabel } from './offboarding-presenters';
import { OffboardingTaskControls } from './offboarding-task-controls';

export function OffboardingTaskList({
    offboardingId,
    offboardingStatus,
    archived,
    tasks,
    assigneeOptions,
    canUpdateTasks,
    canSkipRequired,
}: {
    offboardingId: number;
    offboardingStatus: string;
    archived: boolean;
    tasks: OffboardingTaskRow[];
    assigneeOptions: IdName[];
    canUpdateTasks: boolean;
    canSkipRequired: boolean;
}) {
    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2 text-lg">
                    <ClipboardList className="size-5" /> Checklist snapshot
                </CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
                {tasks.length === 0 && (
                    <div className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm" role="status">
                        Checklist offboarding ini kosong.
                    </div>
                )}
                {tasks.map((task) => {
                    const terminal = task.status === 'COMPLETED' || task.status === 'SKIPPED';

                    return (
                        <div key={task.id} className="flex gap-3 rounded-lg border p-4">
                            {terminal ? (
                                <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-emerald-600" aria-hidden="true" />
                            ) : (
                                <Clock3
                                    className={`mt-0.5 size-5 shrink-0 ${task.overdue ? 'text-destructive' : 'text-muted-foreground'}`}
                                    aria-hidden="true"
                                />
                            )}
                            <div className="min-w-0 flex-1">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-medium">{task.title}</span>
                                    <Badge variant={task.required ? 'default' : 'outline'}>{task.required ? 'Wajib' : 'Opsional'}</Badge>
                                    <Badge variant="secondary">{offboardingTaskStatusLabel(task.status)}</Badge>
                                    {task.overdue && <Badge variant="destructive">Terlambat</Badge>}
                                </div>
                                {task.description && <p className="text-muted-foreground mt-1 text-sm">{task.description}</p>}
                                <p className="text-muted-foreground mt-2 text-xs">
                                    {task.category} · Jatuh tempo {task.due_date} · Offset {task.due_offset_days >= 0 ? '+' : ''}
                                    {task.due_offset_days} hari
                                </p>
                                <p className="text-muted-foreground mt-1 text-xs">
                                    Default assignment: {task.default_assignee_role ?? 'Belum ditentukan'}
                                </p>
                                <p className="text-muted-foreground mt-1 text-xs">Assignee: {task.assignee?.name ?? 'Belum ditugaskan'}</p>
                                {task.completed_by && (
                                    <p className="text-muted-foreground mt-1 text-xs">
                                        Diselesaikan oleh {task.completed_by.name}
                                        {task.completed_at ? ` pada ${task.completed_at}` : ''}
                                    </p>
                                )}
                                {task.completion_note && (
                                    <p className="mt-2 rounded-md bg-emerald-50 p-2 text-xs text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-100">
                                        {task.completion_note}
                                    </p>
                                )}
                                {task.skipped_by && (
                                    <div className="mt-2 rounded-md bg-amber-50 p-2 text-xs text-amber-900 dark:bg-amber-950/40 dark:text-amber-100">
                                        Di-skip oleh {task.skipped_by.name}
                                        {task.skipped_at ? ` pada ${new Date(task.skipped_at).toLocaleString('id-ID')}` : ''}
                                        {task.skip_reason && <p className="mt-1">{task.skip_reason}</p>}
                                    </div>
                                )}
                                {task.reopened_by && (
                                    <div className="mt-2 rounded-md bg-sky-50 p-2 text-xs text-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                                        Dibuka kembali oleh {task.reopened_by.name}
                                        {task.reopened_at ? ` pada ${new Date(task.reopened_at).toLocaleString('id-ID')}` : ''}
                                        {task.reopen_reason && <p className="mt-1">{task.reopen_reason}</p>}
                                    </div>
                                )}
                                <OffboardingTaskControls
                                    offboardingId={offboardingId}
                                    offboardingStatus={offboardingStatus}
                                    archived={archived}
                                    task={task}
                                    assigneeOptions={assigneeOptions}
                                    hasPermission={canUpdateTasks}
                                    canSkipRequired={canSkipRequired}
                                />
                            </div>
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
