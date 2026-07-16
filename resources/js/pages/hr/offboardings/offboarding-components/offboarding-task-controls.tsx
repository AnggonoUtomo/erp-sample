import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { router } from '@inertiajs/react';
import { Check, Play } from 'lucide-react';
import { useState } from 'react';
import type { IdName, OffboardingTaskRow } from '../types';
import { OffboardingTaskReasonDialog } from './offboarding-task-reason-dialog';

export function availableOffboardingTaskActions(
    offboardingStatus: string,
    taskStatus: string,
    taskRequired: boolean,
    archived: boolean,
    hasPermission: boolean,
    canSkipRequired: boolean,
) {
    const assignable = hasPermission && !archived && ['DRAFT', 'IN_PROGRESS'].includes(offboardingStatus);
    const lifecycle = hasPermission && !archived && offboardingStatus === 'IN_PROGRESS';
    const reopenable = hasPermission && !archived && ['IN_PROGRESS', 'READY_FOR_EXIT'].includes(offboardingStatus);
    const terminal = ['COMPLETED', 'SKIPPED'].includes(taskStatus);

    return {
        assign: assignable && !terminal,
        start: lifecycle && taskStatus === 'PENDING',
        complete: lifecycle && taskStatus === 'IN_PROGRESS',
        skip: lifecycle && ['PENDING', 'IN_PROGRESS'].includes(taskStatus) && (!taskRequired || canSkipRequired),
        reopen: reopenable && terminal,
    };
}

export function OffboardingTaskControls({
    offboardingId,
    offboardingStatus,
    archived,
    task,
    assigneeOptions,
    hasPermission,
    canSkipRequired,
}: {
    offboardingId: number;
    offboardingStatus: string;
    archived: boolean;
    task: OffboardingTaskRow;
    assigneeOptions: IdName[];
    hasPermission: boolean;
    canSkipRequired: boolean;
}) {
    const actions = availableOffboardingTaskActions(offboardingStatus, task.status, task.required, archived, hasPermission, canSkipRequired);
    const [completionOpen, setCompletionOpen] = useState(false);
    const [completionNote, setCompletionNote] = useState('');
    const [processing, setProcessing] = useState(false);

    if (!actions.assign && !actions.start && !actions.complete && !actions.skip && !actions.reopen) return null;

    const routeParams = { offboarding: offboardingId, task: task.id };
    const options = {
        preserveScroll: true,
        onStart: () => setProcessing(true),
        onFinish: () => setProcessing(false),
    };

    return (
        <div className="mt-3 flex flex-wrap items-center gap-2 border-t pt-3">
            {actions.assign && (
                <Select
                    value={task.assignee ? String(task.assignee.id) : 'none'}
                    disabled={processing}
                    onValueChange={(value) =>
                        router.patch(
                            route('hr.offboardings.tasks.assignment', routeParams),
                            { assignee_user_id: value === 'none' ? null : Number(value) },
                            options,
                        )
                    }
                >
                    <SelectTrigger className="w-[210px]" aria-label="Assignee task">
                        <SelectValue placeholder="Pilih assignee" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">Belum ditugaskan</SelectItem>
                        {assigneeOptions.map((user) => (
                            <SelectItem key={user.id} value={String(user.id)}>
                                {user.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}
            {actions.start && (
                <Button
                    size="sm"
                    variant="outline"
                    disabled={processing}
                    onClick={() => router.patch(route('hr.offboardings.tasks.start', routeParams), {}, options)}
                >
                    <Play className="size-4" /> Mulai task
                </Button>
            )}
            {actions.complete && (
                <Dialog open={completionOpen} onOpenChange={setCompletionOpen}>
                    <DialogTrigger asChild>
                        <Button size="sm">
                            <Check className="size-4" /> Selesaikan
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Selesaikan task?</DialogTitle>
                            <DialogDescription>
                                Completion menyimpan actor, waktu, dan catatan ringkas sebagai evidence offboarding.
                            </DialogDescription>
                        </DialogHeader>
                        <div className="space-y-2">
                            <Label htmlFor={`completion-note-${task.id}`}>Catatan completion (opsional)</Label>
                            <Textarea
                                id={`completion-note-${task.id}`}
                                value={completionNote}
                                onChange={(event) => setCompletionNote(event.target.value)}
                                maxLength={2000}
                                autoFocus
                            />
                        </div>
                        <DialogFooter>
                            <DialogClose asChild>
                                <Button type="button" variant="outline" disabled={processing}>
                                    Batal
                                </Button>
                            </DialogClose>
                            <Button
                                type="button"
                                disabled={processing}
                                onClick={() =>
                                    router.patch(
                                        route('hr.offboardings.tasks.complete', routeParams),
                                        { completion_note: completionNote || null },
                                        {
                                            ...options,
                                            onSuccess: () => {
                                                setCompletionOpen(false);
                                                setCompletionNote('');
                                            },
                                        },
                                    )
                                }
                            >
                                {processing ? 'Menyimpan…' : 'Ya, selesaikan'}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            )}
            {actions.skip && <OffboardingTaskReasonDialog action="skip" offboardingId={offboardingId} taskId={task.id} />}
            {actions.reopen && <OffboardingTaskReasonDialog action="reopen" offboardingId={offboardingId} taskId={task.id} />}
        </div>
    );
}
