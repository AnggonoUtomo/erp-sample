<?php

namespace App\Modules\HR\EmploymentTypes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmploymentTypes\Http\Requests\StoreEmploymentTypeRequest;
use App\Modules\HR\EmploymentTypes\Http\Requests\UpdateEmploymentTypeRequest;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\EmploymentTypes\Services\EmploymentTypesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmploymentTypesController extends Controller
{
    public function __construct(
        private readonly EmploymentTypesService $employmentTypes,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmploymentType::class);

        return Inertia::render('hr/employment-types/index', $this->employmentTypes->getPageData($request->query()));
    }

    public function store(StoreEmploymentTypeRequest $request): RedirectResponse
    {
        $employmentType = $this->employmentTypes->create($request->toDto());

        return back()->with('success', "Employment type {$employmentType->name} berhasil dibuat.");
    }

    public function update(UpdateEmploymentTypeRequest $request, EmploymentType $employmentType): RedirectResponse
    {
        $employmentType = $this->employmentTypes->update($employmentType, $request->toDto());

        return back()->with('success', "Employment type {$employmentType->name} berhasil diperbarui.");
    }

    public function destroy(EmploymentType $employmentType): RedirectResponse
    {
        $this->authorize('delete', $employmentType);

        $this->employmentTypes->delete($employmentType);

        return back()->with('success', "Employment type {$employmentType->name} berhasil diarsipkan.");
    }

    public function restore(int $employmentType): RedirectResponse
    {
        $model = EmploymentType::withTrashed()->findOrFail($employmentType);

        $this->authorize('restore', $model);

        $this->employmentTypes->restore($model);

        return back()->with('success', "Employment type {$model->name} berhasil dipulihkan.");
    }

    public function forceDestroy(int $employmentType): RedirectResponse
    {
        $model = EmploymentType::withTrashed()->findOrFail($employmentType);

        $this->authorize('forceDelete', $model);

        $this->employmentTypes->forceDelete($model);

        return back()->with('success', "Employment type {$model->name} berhasil dihapus permanen.");
    }
}
