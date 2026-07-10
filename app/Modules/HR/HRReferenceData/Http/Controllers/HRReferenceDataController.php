<?php

namespace App\Modules\HR\HRReferenceData\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\HRReferenceData\Http\Requests\StoreReferenceCategoryRequest;
use App\Modules\HR\HRReferenceData\Http\Requests\StoreReferenceDataRequest;
use App\Modules\HR\HRReferenceData\Http\Requests\UpdateReferenceCategoryRequest;
use App\Modules\HR\HRReferenceData\Http\Requests\UpdateReferenceDataRequest;
use App\Modules\HR\HRReferenceData\Models\ReferenceCategory;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\HRReferenceData\Services\HRReferenceDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRReferenceDataController extends Controller
{
    public function __construct(
        private readonly HRReferenceDataService $referenceData,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', ReferenceData::class);

        return Inertia::render('hr/hr-reference-data/index', $this->referenceData->getPageData($request->query()));
    }

    public function store(StoreReferenceDataRequest $request): RedirectResponse
    {
        $referenceData = $this->referenceData->create($request->toDto());

        return back()->with('success', "Reference data {$referenceData->name} berhasil dibuat.");
    }

    public function update(UpdateReferenceDataRequest $request, ReferenceData $referenceData): RedirectResponse
    {
        $referenceData = $this->referenceData->update($referenceData, $request->toDto());

        return back()->with('success', "Reference data {$referenceData->name} berhasil diperbarui.");
    }

    public function destroy(ReferenceData $referenceData): RedirectResponse
    {
        $this->authorize('delete', $referenceData);

        $this->referenceData->delete($referenceData);

        return back()->with('success', "Reference data {$referenceData->name} berhasil diarsipkan.");
    }

    public function restore(int $referenceData): RedirectResponse
    {
        $model = ReferenceData::withTrashed()->findOrFail($referenceData);

        $this->authorize('restore', $model);

        $this->referenceData->restore($model);

        return back()->with('success', "Reference data {$model->name} berhasil dipulihkan.");
    }

    public function forceDestroy(int $referenceData): RedirectResponse
    {
        $model = ReferenceData::withTrashed()->findOrFail($referenceData);

        $this->authorize('forceDelete', $model);

        $this->referenceData->forceDelete($model);

        return back()->with('success', "Reference data {$model->name} berhasil dihapus permanen.");
    }

    public function storeCategory(StoreReferenceCategoryRequest $request): RedirectResponse
    {
        $category = $this->referenceData->createCategory($request->toDto());

        return back()->with('success', "Kategori {$category->name} berhasil dibuat.");
    }

    public function updateCategory(UpdateReferenceCategoryRequest $request, ReferenceCategory $referenceCategory): RedirectResponse
    {
        $category = $this->referenceData->updateCategory($referenceCategory, $request->toDto());

        return back()->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    public function destroyCategory(ReferenceCategory $referenceCategory): RedirectResponse
    {
        abort_unless(request()->user()?->hasAnyPermission(['hr-reference-data.delete', 'hr-reference-data.manage']), 403);

        $this->referenceData->deleteCategory($referenceCategory);

        return back()->with('success', "Kategori {$referenceCategory->name} berhasil dihapus.");
    }
}
