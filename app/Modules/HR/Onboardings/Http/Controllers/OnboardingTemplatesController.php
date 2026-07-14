<?php

namespace App\Modules\HR\Onboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Onboardings\Http\Requests\StoreOnboardingTemplateRequest;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Services\OnboardingTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            new Middleware('can:delete,template', only: ['archive']),
            new Middleware('can:restore,template', only: ['restore']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('hr/onboardings/templates/index', $this->templates->getPageData($request->boolean('archived')));
    }

    public function store(StoreOnboardingTemplateRequest $request): RedirectResponse
    {
        $this->templates->create($request->toDto());

        return redirect()->route('hr.onboardings.templates.index')->with('success', 'Template onboarding berhasil dibuat.');
    }

    public function archive(OnboardingTemplate $template): RedirectResponse
    {
        $this->templates->archive($template);

        return back()->with('success', 'Template onboarding berhasil diarsipkan.');
    }

    public function restore(OnboardingTemplate $template): RedirectResponse
    {
        abort_unless($template->trashed(), 404);
        $this->templates->restore($template);

        return back()->with('success', 'Template onboarding berhasil direstore.');
    }
}
