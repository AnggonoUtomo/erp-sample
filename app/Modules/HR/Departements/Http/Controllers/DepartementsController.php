<?php

namespace App\Modules\HR\Departements\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Departements\Http\Requests\StoreDepartementRequest;
use App\Modules\HR\Departements\Http\Requests\UpdateDepartementRequest;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Departements\Services\DepartementsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class DepartementsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly DepartementsService $departements,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Departement::class, only: ['index']),
            new Middleware('can:create,'.Departement::class, only: ['store']),
            new Middleware('can:update,departement', only: ['update']),
            new Middleware('can:delete,departement', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('hr/departements/index', $this->departements->getPageData($request->only(['search', 'status', 'per_page'])));
    }

    public function store(StoreDepartementRequest $request): RedirectResponse
    {
        $this->departements->create($request->toDto());

        return back()->with('success', 'Departement berhasil dibuat.');
    }

    public function update(UpdateDepartementRequest $request, Departement $departement): RedirectResponse
    {
        $this->departements->update($departement, $request->toDto());

        return back()->with('success', 'Departement berhasil diperbarui.');
    }

    public function destroy(Departement $departement): RedirectResponse
    {
        $this->departements->delete($departement);

        return back()->with('success', 'Departement berhasil dihapus.');
    }
}
