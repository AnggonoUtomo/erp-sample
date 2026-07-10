<?php

namespace App\Modules\HR\Positions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Positions\Http\Requests\StorePositionRequest;
use App\Modules\HR\Positions\Http\Requests\UpdatePositionRequest;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\Positions\Services\PositionsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class PositionsController extends Controller implements HasMiddleware
{
    public function __construct(
        private readonly PositionsService $positions,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:viewAny,'.Position::class, only: ['index']),
            new Middleware('can:create,'.Position::class, only: ['store']),
            new Middleware('can:update,position', only: ['update']),
            new Middleware('can:delete,position', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        return Inertia::render('hr/positions/index', $this->positions->getPageData($request->only(['search', 'status', 'departement', 'per_page'])));
    }

    public function store(StorePositionRequest $request): RedirectResponse
    {
        $this->positions->create($request->toDto());

        return back()->with('success', 'Position berhasil dibuat.');
    }

    public function update(UpdatePositionRequest $request, Position $position): RedirectResponse
    {
        $this->positions->update($position, $request->toDto());

        return back()->with('success', 'Position berhasil diperbarui.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        $this->positions->delete($position);

        return back()->with('success', 'Position berhasil dihapus.');
    }
}
