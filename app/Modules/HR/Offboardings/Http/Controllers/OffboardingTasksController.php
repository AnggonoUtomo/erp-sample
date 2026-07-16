<?php

namespace App\Modules\HR\Offboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Offboardings\Http\Requests\AssignOffboardingTaskRequest;
use App\Modules\HR\Offboardings\Http\Requests\CompleteOffboardingTaskRequest;
use App\Modules\HR\Offboardings\Http\Requests\ReopenOffboardingTaskRequest;
use App\Modules\HR\Offboardings\Http\Requests\SkipOffboardingTaskRequest;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Services\OffboardingTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OffboardingTasksController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OffboardingTaskService $tasks) {}

    public static function middleware(): array
    {
        return [new Middleware('can:update,task')];
    }

    public function assignment(
        AssignOffboardingTaskRequest $request,
        Offboarding $offboarding,
        OffboardingTask $task,
    ): RedirectResponse {
        $this->tasks->assign($offboarding, $task, $request->toDto());

        return $this->toDetail($offboarding, 'Assignee task berhasil diperbarui.');
    }

    public function start(Offboarding $offboarding, OffboardingTask $task): RedirectResponse
    {
        $this->tasks->start($offboarding, $task);

        return $this->toDetail($offboarding, 'Task berhasil dimulai.');
    }

    public function complete(
        CompleteOffboardingTaskRequest $request,
        Offboarding $offboarding,
        OffboardingTask $task,
    ): RedirectResponse {
        $this->tasks->complete($offboarding, $task, $request->toDto());

        return $this->toDetail($offboarding, 'Task berhasil diselesaikan.');
    }

    public function skip(
        SkipOffboardingTaskRequest $request,
        Offboarding $offboarding,
        OffboardingTask $task,
    ): RedirectResponse {
        $this->tasks->skip($offboarding, $task, $request->toDto());

        return $this->toDetail($offboarding, 'Task berhasil di-skip.');
    }

    public function reopen(
        ReopenOffboardingTaskRequest $request,
        Offboarding $offboarding,
        OffboardingTask $task,
    ): RedirectResponse {
        $this->tasks->reopen($offboarding, $task, $request->toDto());

        return $this->toDetail($offboarding, 'Task berhasil dibuka kembali.');
    }

    private function toDetail(Offboarding $offboarding, string $message): RedirectResponse
    {
        return redirect()->route('hr.offboardings.show', [
            'offboarding' => $offboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', $message);
    }
}
