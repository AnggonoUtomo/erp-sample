<?php

namespace App\Modules\HR\Onboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Onboardings\Http\Requests\AssignOnboardingTaskRequest;
use App\Modules\HR\Onboardings\Http\Requests\CompleteOnboardingTaskRequest;
use App\Modules\HR\Onboardings\Http\Requests\ReopenOnboardingTaskRequest;
use App\Modules\HR\Onboardings\Http\Requests\SkipOnboardingTaskRequest;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Services\OnboardingTaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OnboardingTasksController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnboardingTaskService $tasks) {}

    public static function middleware(): array
    {
        return [new Middleware('can:update,task')];
    }

    public function assignment(AssignOnboardingTaskRequest $request, Onboarding $onboarding, OnboardingTask $task): RedirectResponse
    {
        $this->tasks->assign($onboarding, $task, $request->toDto());

        return $this->toDetail($onboarding, 'Assignee task berhasil diperbarui.');
    }

    public function start(Onboarding $onboarding, OnboardingTask $task): RedirectResponse
    {
        $this->tasks->start($onboarding, $task);

        return $this->toDetail($onboarding, 'Task berhasil dimulai.');
    }

    public function complete(CompleteOnboardingTaskRequest $request, Onboarding $onboarding, OnboardingTask $task): RedirectResponse
    {
        $this->tasks->complete($onboarding, $task, $request->toDto());

        return $this->toDetail($onboarding, 'Task berhasil diselesaikan.');
    }

    public function skip(SkipOnboardingTaskRequest $request, Onboarding $onboarding, OnboardingTask $task): RedirectResponse
    {
        $this->tasks->skip($onboarding, $task, $request->toDto());

        return $this->toDetail($onboarding, 'Task berhasil di-skip.');
    }

    public function reopen(ReopenOnboardingTaskRequest $request, Onboarding $onboarding, OnboardingTask $task): RedirectResponse
    {
        $this->tasks->reopen($onboarding, $task, $request->toDto());

        return $this->toDetail($onboarding, 'Task berhasil dibuka kembali.');
    }

    private function toDetail(Onboarding $onboarding, string $message): RedirectResponse
    {
        return redirect()->route('hr.onboardings.show', [
            'onboarding' => $onboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', $message);
    }
}
