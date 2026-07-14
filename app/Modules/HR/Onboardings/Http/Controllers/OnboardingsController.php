<?php

namespace App\Modules\HR\Onboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Onboardings\Http\Requests\ShowOnboardingRequest;
use App\Modules\HR\Onboardings\Http\Requests\StoreOnboardingDraftRequest;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Services\OnboardingProgressReadService;
use App\Modules\HR\Onboardings\Services\OnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingsController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnboardingService $onboardings, private readonly OnboardingProgressReadService $progress) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Onboarding::class, only: ['index']),
            new Middleware('can:view,onboarding', only: ['show']),
            new Middleware('can:create,'.Onboarding::class, only: ['store']),
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
            'businessDate' => $businessDate,
            'onboarding' => $this->progress->detail($onboarding, $businessDate),
        ]);
    }
}
