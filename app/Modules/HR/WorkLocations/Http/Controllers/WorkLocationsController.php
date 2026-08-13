<?php

namespace App\Modules\HR\WorkLocations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\WorkLocations\Application\Services\WorkLocationsService;
use App\Modules\HR\WorkLocations\Http\Requests\StoreWorkLocationRequest;
use App\Modules\HR\WorkLocations\Http\Requests\UpdateWorkLocationRequest;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class WorkLocationsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly WorkLocationsService $workLocations,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.WorkLocation::class, only: ['index']),
            new Middleware('can:create,'.WorkLocation::class, only: ['store']),
            new Middleware('can:update,workLocation', only: ['update']),
            new Middleware('can:delete,workLocation', only: ['destroy']),
            new Middleware('can:restore,workLocation', only: ['restore']),
            new Middleware('can:forceDelete,workLocation', only: ['forceDestroy']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('hr/work-locations/index', $this->workLocations->getPageData($request->only(['search', 'status', 'archive', 'city', 'per_page'])));
    }

    public function store(StoreWorkLocationRequest $request): RedirectResponse
    {
        $this->workLocations->create($request->toDto());

        return back()->with('success', 'Work location berhasil dibuat.');
    }

    public function update(UpdateWorkLocationRequest $request, WorkLocation $workLocation): RedirectResponse
    {
        $this->workLocations->update($workLocation, $request->toDto());

        return back()->with('success', 'Work location berhasil diperbarui.');
    }

    public function destroy(WorkLocation $workLocation): RedirectResponse
    {
        $this->workLocations->delete($workLocation);

        return back()->with('success', 'Work location dipindahkan ke arsip.');
    }

    public function restore(WorkLocation $workLocation): RedirectResponse
    {
        $this->workLocations->restore($workLocation);

        return back()->with('success', 'Work location berhasil dipulihkan.');
    }

    public function forceDestroy(WorkLocation $workLocation): RedirectResponse
    {
        $this->workLocations->forceDelete($workLocation);

        return back()->with('success', 'Work location berhasil dihapus permanen.');
    }
}
