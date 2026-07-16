<?php

namespace App\Modules\HR\Offboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\HR\Offboardings\Http\Requests\CancelOffboardingRequest;
use App\Modules\HR\Offboardings\Http\Requests\FinalizeOffboardingRequest;
use App\Modules\HR\Offboardings\Http\Requests\ShowOffboardingRequest;
use App\Modules\HR\Offboardings\Http\Requests\StoreOffboardingDraftRequest;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Services\OffboardingActivationService;
use App\Modules\HR\Offboardings\Services\OffboardingFinalizationService;
use App\Modules\HR\Offboardings\Services\OffboardingLifecycleService;
use App\Modules\HR\Offboardings\Services\OffboardingProgressReadService;
use App\Modules\HR\Offboardings\Services\OffboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OffboardingsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly OffboardingService $offboardings,
        private readonly OffboardingProgressReadService $progress,
        private readonly OffboardingActivationService $activation,
        private readonly OffboardingLifecycleService $lifecycle,
        private readonly OffboardingFinalizationService $finalization,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Offboarding::class, only: ['index']),
            new Middleware('can:view,offboarding', only: ['show']),
            new Middleware('can:create,'.Offboarding::class, only: ['store']),
            new Middleware('can:activate,offboarding', only: ['activate']),
            new Middleware('can:markReady,offboarding', only: ['markReady']),
            new Middleware('can:cancel,offboarding', only: ['cancel']),
            new Middleware('can:finalize,offboarding', only: ['finalize']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('hr/offboardings/index', $this->offboardings->getPageData());
    }

    public function store(StoreOffboardingDraftRequest $request): RedirectResponse
    {
        $this->offboardings->createDraft($request->toDto());

        return redirect()->route('hr.offboardings.index')->with('success', 'Draft offboarding berhasil dibuat.');
    }

    public function show(ShowOffboardingRequest $request, Offboarding $offboarding): Response
    {
        $businessDate = $request->validated('business_date');

        return Inertia::render('hr/offboardings/show', [
            'assigneeOptions' => Gate::allows('update', OffboardingTask::class)
                ? User::query()->orderBy('name')->get(['id', 'name'])
                : [],
            'businessDate' => $businessDate,
            'offboarding' => $this->progress->detail($offboarding, $businessDate),
        ]);
    }

    public function activate(Offboarding $offboarding): RedirectResponse
    {
        $this->activation->activate($offboarding);

        return redirect()->route('hr.offboardings.show', [
            'offboarding' => $offboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', 'Offboarding berhasil diaktifkan.');
    }

    public function markReady(Offboarding $offboarding): RedirectResponse
    {
        $this->lifecycle->markReady($offboarding);

        return redirect()->route('hr.offboardings.show', [
            'offboarding' => $offboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', 'Offboarding siap untuk proses final exit.');
    }

    public function cancel(CancelOffboardingRequest $request, Offboarding $offboarding): RedirectResponse
    {
        $this->lifecycle->cancel($offboarding, $request->toDto());

        return redirect()->route('hr.offboardings.show', [
            'offboarding' => $offboarding,
            'business_date' => now()->toDateString(),
        ])->with('success', 'Offboarding berhasil dibatalkan.');
    }

    public function finalize(FinalizeOffboardingRequest $request, Offboarding $offboarding): RedirectResponse
    {
        $this->finalization->finalize($offboarding, $request->toDto());

        return redirect()->route('hr.offboardings.show', [
            'offboarding' => $offboarding,
            'business_date' => $request->validated('business_date'),
        ])->with('success', 'Employment exit berhasil difinalisasi.');
    }
}
