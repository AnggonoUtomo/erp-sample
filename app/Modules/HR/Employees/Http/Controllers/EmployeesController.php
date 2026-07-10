<?php

namespace App\Modules\HR\Employees\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\Employees\Http\Requests\StoreEmployeeRequest;
use App\Modules\HR\Employees\Http\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Employees\Services\EmployeesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeesController extends Controller
{
    public function __construct(
        private readonly EmployeesService $employees,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Employee::class);

        return Inertia::render('hr/employees/index', $this->employees->getPageData($request->query()));
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employees->create($request->toDto());

        return back()->with('success', "Employee {$employee->display_name} berhasil dibuat.");
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $employee = $this->employees->update($employee, $request->toDto());

        return back()->with('success', "Employee {$employee->display_name} berhasil diperbarui.");
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $this->employees->delete($employee);

        return back()->with('success', "Employee {$employee->display_name} berhasil diarsipkan.");
    }

    public function restore(int $employee): RedirectResponse
    {
        $model = Employee::withTrashed()->findOrFail($employee);

        $this->authorize('restore', $model);

        $this->employees->restore($model);

        return back()->with('success', "Employee {$model->display_name} berhasil dipulihkan.");
    }

    public function forceDestroy(int $employee): RedirectResponse
    {
        $model = Employee::withTrashed()->findOrFail($employee);

        $this->authorize('forceDelete', $model);

        $this->employees->forceDelete($model);

        return back()->with('success', "Employee {$model->display_name} berhasil dihapus permanen.");
    }
}
