<?php

namespace App\Modules\HR\Offboardings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Offboardings\Http\Requests\StoreOffboardingTemplateRequest;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Services\OffboardingTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class OffboardingTemplatesController extends Controller implements HasMiddleware
{
    public function __construct(private readonly OffboardingTemplateService $templates) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.OffboardingTemplate::class, only: ['index']),
            new Middleware('can:create,'.OffboardingTemplate::class, only: ['store']),
            new Middleware('can:delete,template', only: ['archive']),
            new Middleware('can:restore,template', only: ['restore']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render(
            'hr/offboardings/templates/index',
            $this->templates->getPageData($request->boolean('archived')),
        );
    }

    public function store(StoreOffboardingTemplateRequest $request): RedirectResponse
    {
        $this->templates->create($request->toDto());

        return redirect()
            ->route('hr.offboardings.templates.index')
            ->with('success', 'Template offboarding berhasil dibuat.');
    }

    public function archive(OffboardingTemplate $template): RedirectResponse
    {
        $this->templates->archive($template);

        return back()->with('success', 'Template offboarding berhasil diarsipkan.');
    }

    public function restore(OffboardingTemplate $template): RedirectResponse
    {
        abort_unless($template->trashed(), 404);
        $this->templates->restore($template);

        return back()->with('success', 'Template offboarding berhasil direstore.');
    }
}
