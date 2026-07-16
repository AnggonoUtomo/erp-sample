<?php

namespace App\Modules\HR\Offboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Offboardings\Http\Requests\ShowOffboardingRequest;
use App\Modules\HR\Offboardings\Http\Requests\StoreOffboardingDraftRequest;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Services\OffboardingProgressReadService;
use App\Modules\HR\Offboardings\Services\OffboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class OffboardingsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly OffboardingService $offboardings,
        private readonly OffboardingProgressReadService $progress,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Offboarding::class, only: ['index']),
            new Middleware('can:view,offboarding', only: ['show']),
            new Middleware('can:create,'.Offboarding::class, only: ['store']),
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
            'businessDate' => $businessDate,
            'offboarding' => $this->progress->detail($offboarding, $businessDate),
        ]);
    }
}
