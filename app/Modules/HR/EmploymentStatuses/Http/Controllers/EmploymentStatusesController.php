<?php

namespace App\Modules\HR\EmploymentStatuses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmploymentStatuses\Http\Requests\StoreEmploymentStatusRequest;
use App\Modules\HR\EmploymentStatuses\Http\Requests\UpdateEmploymentStatusRequest;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentStatuses\Services\EmploymentStatusesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmploymentStatusesController extends Controller
{
    public function __construct(
        private readonly EmploymentStatusesService $employmentStatuses,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmploymentStatus::class);

        return Inertia::render('hr/employment-statuses/index', $this->employmentStatuses->getPageData($request->query()));
    }

    public function store(StoreEmploymentStatusRequest $request): RedirectResponse
    {
        $employmentStatus = $this->employmentStatuses->create($request->toDto());

        return back()->with('success', "Employment status {$employmentStatus->name} berhasil dibuat.");
    }

    public function update(UpdateEmploymentStatusRequest $request, EmploymentStatus $employmentStatus): RedirectResponse
    {
        $employmentStatus = $this->employmentStatuses->update($employmentStatus, $request->toDto());

        return back()->with('success', "Employment status {$employmentStatus->name} berhasil diperbarui.");
    }

    public function destroy(EmploymentStatus $employmentStatus): RedirectResponse
    {
        $this->authorize('delete', $employmentStatus);

        $this->employmentStatuses->delete($employmentStatus);

        return back()->with('success', "Employment status {$employmentStatus->name} berhasil diarsipkan.");
    }

    public function restore(int $employmentStatus): RedirectResponse
    {
        $model = EmploymentStatus::withTrashed()->findOrFail($employmentStatus);

        $this->authorize('restore', $model);

        $this->employmentStatuses->restore($model);

        return back()->with('success', "Employment status {$model->name} berhasil dipulihkan.");
    }

    public function forceDestroy(int $employmentStatus): RedirectResponse
    {
        $model = EmploymentStatus::withTrashed()->findOrFail($employmentStatus);

        $this->authorize('forceDelete', $model);

        $this->employmentStatuses->forceDelete($model);

        return back()->with('success', "Employment status {$model->name} berhasil dihapus permanen.");
    }
}
