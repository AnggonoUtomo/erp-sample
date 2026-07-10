<?php

namespace App\Modules\HR\OrganizationStructures\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\OrganizationStructures\Http\Requests\StoreOrganizationStructureRequest;
use App\Modules\HR\OrganizationStructures\Http\Requests\UpdateOrganizationStructureRequest;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use App\Modules\HR\OrganizationStructures\Services\OrganizationStructuresService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationStructuresController extends Controller
{
    public function __construct(private readonly OrganizationStructuresService $organizationStructures) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', OrganizationStructure::class);

        return Inertia::render('hr/organization-structures/index', $this->organizationStructures->getPageData($request->query()));
    }

    public function store(StoreOrganizationStructureRequest $request): RedirectResponse
    {
        $organizationStructure = $this->organizationStructures->create($request->toDto());

        return back()->with('success', "Organization structure {$organizationStructure->name} berhasil dibuat.");
    }

    public function update(UpdateOrganizationStructureRequest $request, OrganizationStructure $organizationStructure): RedirectResponse
    {
        $organizationStructure = $this->organizationStructures->update($organizationStructure, $request->toDto());

        return back()->with('success', "Organization structure {$organizationStructure->name} berhasil diperbarui.");
    }

    public function destroy(OrganizationStructure $organizationStructure): RedirectResponse
    {
        $this->authorize('delete', $organizationStructure);
        $this->organizationStructures->delete($organizationStructure);

        return back()->with('success', "Organization structure {$organizationStructure->name} berhasil diarsipkan.");
    }

    public function restore(int $organizationStructure): RedirectResponse
    {
        $model = OrganizationStructure::withTrashed()->findOrFail($organizationStructure);
        $this->authorize('restore', $model);
        $this->organizationStructures->restore($model);

        return back()->with('success', "Organization structure {$model->name} berhasil dipulihkan.");
    }

    public function forceDestroy(int $organizationStructure): RedirectResponse
    {
        $model = OrganizationStructure::withTrashed()->findOrFail($organizationStructure);
        $this->authorize('forceDelete', $model);
        $this->organizationStructures->forceDelete($model);

        return back()->with('success', "Organization structure {$model->name} berhasil dihapus permanen.");
    }
}
