<?php

namespace App\Modules\HR\Onboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Onboardings\Http\Requests\StoreOnboardingTemplateRequest;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Services\OnboardingTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingTemplatesController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OnboardingTemplateService $templates) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.OnboardingTemplate::class, only: ['index']),
            new Middleware('can:create,'.OnboardingTemplate::class, only: ['store']),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('hr/onboardings/templates/index', $this->templates->getPageData());
    }

    public function store(StoreOnboardingTemplateRequest $request): RedirectResponse
    {
        $this->templates->create($request->toDto());

        return redirect()->route('hr.onboardings.templates.index')->with('success', 'Template onboarding berhasil dibuat.');
    }
}
