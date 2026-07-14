<?php

namespace App\Modules\HR\Onboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Onboardings\Http\Requests\ShowOnboardingRequest;
use App\Modules\HR\Onboardings\Http\Requests\StoreOnboardingDraftRequest;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Services\OnboardingActivationService;
use App\Modules\HR\Onboardings\Services\OnboardingProgressReadService;
use App\Modules\HR\Onboardings\Services\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingsController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnboardingService $onboardings, private readonly OnboardingProgressReadService $progress, private readonly OnboardingActivationService $activation) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Onboarding::class, only: ['index']),
            new Middleware('can:view,onboarding', only: ['show']),
            new Middleware('can:create,'.Onboarding::class, only: ['store']),
            new Middleware('can:activate,onboarding', only: ['activate']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('hr/onboardings/index', $this->onboardings->getPageData());
    }

    public function store(StoreOnboardingDraftRequest $request): RedirectResponse
    {
        $this->onboardings->createDraft($request->toDto());

        return redirect()->route('hr.onboardings.index')->with('success', 'Draft onboarding berhasil dibuat.');
    }

    public function show(ShowOnboardingRequest $request, Onboarding $onboarding): Response
    {
        $businessDate = $request->validated('business_date');

        return Inertia::render('hr/onboardings/show', [
            'assigneeOptions' => Gate::allows('update', OnboardingTask::class)
                ? User::query()->orderBy('name')->get(['id', 'name'])
                : [],
            'businessDate' => $businessDate,
            'onboarding' => $this->progress->detail($onboarding, $businessDate),
        ]);
    }

    public function activate(Onboarding $onboarding): RedirectResponse
    {
        $this->activation->activate($onboarding);

        return redirect()->route('hr.onboardings.show', [
            'onboarding' => $onboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', 'Onboarding berhasil diaktifkan.');
    }
}
