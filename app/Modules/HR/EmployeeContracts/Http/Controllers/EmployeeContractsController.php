<?php

namespace App\Modules\HR\EmployeeContracts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmployeeContracts\Http\Requests\StoreEmployeeContractRequest;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Services\EmployeeContractsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeContractsController extends Controller
{
    public function __construct(private EmployeeContractsService $contracts) {}

    public function index(): Response
    {
        $this->authorize('viewAny', EmployeeContract::class);

        return Inertia::render('hr/employee-contracts/index', $this->contracts->pageData());
    }

    public function store(StoreEmployeeContractRequest $request): RedirectResponse
    {
        $contract = $this->contracts->create($request->toDto());

        return back()->with('success', "Contract {$contract->contract_number} berhasil dibuat.");
    }
}
