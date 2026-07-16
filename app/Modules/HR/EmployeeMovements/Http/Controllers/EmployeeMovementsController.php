<?php

namespace App\Modules\HR\EmployeeMovements\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmployeeMovements\Http\Requests\CancelEmployeeMovementRequest;
use App\Modules\HR\EmployeeMovements\Http\Requests\StoreEmployeeMovementRequest;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\EmployeeMovements\Services\EmployeeMovementsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeMovementsController extends Controller
{
    public function __construct(private EmployeeMovementsService $movements) {}

    public function index(): Response
    {
        $this->authorize('viewAny', EmployeeMovement::class);

        return Inertia::render('hr/employee-movements/index', $this->movements->pageData());
    }

    public function store(StoreEmployeeMovementRequest $request): RedirectResponse
    {
        $this->movements->create($request->toDto());

        return back()->with('success', 'Draft movement berhasil dibuat.');
    }

    public function apply(EmployeeMovement $employeeMovement): RedirectResponse
    {
        $this->authorize('apply', $employeeMovement);
        $this->movements->apply($employeeMovement);

        return back()->with('success', 'Movement berhasil diterapkan ke profile employee.');
    }

    public function cancel(CancelEmployeeMovementRequest $request, EmployeeMovement $employeeMovement): RedirectResponse
    {
        $this->movements->cancel($employeeMovement, (string) $request->validated('reason'));

        return back()->with('success', 'Movement berhasil dibatalkan.');
    }
}
