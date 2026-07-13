<?php

namespace App\Modules\HR\EmployeeContracts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmployeeContracts\Http\Requests\CancelEmployeeContractRequest;
use App\Modules\HR\EmployeeContracts\Http\Requests\StoreEmployeeContractRequest;
use App\Modules\HR\EmployeeContracts\Http\Requests\TerminateEmployeeContractRequest;
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

    public function activate(EmployeeContract $employeeContract): RedirectResponse
    {
        $this->authorize('activate', $employeeContract);
        $contract = $this->contracts->activate($employeeContract);

        return back()->with('success', "Contract {$contract->contract_number} berhasil diaktifkan.");
    }

    public function terminate(TerminateEmployeeContractRequest $request, EmployeeContract $employeeContract): RedirectResponse
    {
        $contract = $this->contracts->terminate($employeeContract, $request->date('end_date')->toDateString(), $request->string('reason')->trim()->toString());

        return back()->with('success', "Contract {$contract->contract_number} berhasil diakhiri.");
    }

    public function cancel(CancelEmployeeContractRequest $request, EmployeeContract $employeeContract): RedirectResponse
    {
        $contract = $this->contracts->cancel($employeeContract, $request->string('reason')->trim()->toString());

        return back()->with('success', "Contract {$contract->contract_number} berhasil dibatalkan.");
    }
}
