<?php

namespace App\Modules\HR\HRReports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\HRReports\Http\Requests\ListHRReportRequest;
use App\Modules\HR\HRReports\Services\HeadcountReportService;
use Inertia\Inertia;
use Inertia\Response;

class HRReportsController extends Controller
{
    public function __construct(
        private readonly HeadcountReportService $headcount,
    ) {}

    public function index(ListHRReportRequest $request): Response
    {
        $filters = $request->toHeadcountFilters();

        return Inertia::render('hr/hr-reports/index', [
            'meta' => [
                'status' => 'read-only-boundary',
                'scope' => [
                    'Headcount by Departement',
                    'Headcount by Work Location',
                    'Employment Status Summary',
                    'Contract Expiry',
                    'Document Expiry',
                ],
            ],
            'filters' => [
                'as_of' => $filters->asOfDate()->toDateString(),
                'employment_status_id' => $filters->employmentStatusIds[0] ?? null,
            ],
            'options' => [
                'employmentStatuses' => EmploymentStatus::query()
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (EmploymentStatus $status) => [
                        'value' => $status->id,
                        'label' => $status->name,
                    ])
                    ->values(),
            ],
            'headcount' => [
                'byDepartement' => $this->headcount->byDepartement($filters),
                'byWorkLocation' => $this->headcount->byWorkLocation($filters),
                'byEmploymentStatus' => $this->headcount->byEmploymentStatus($filters),
            ],
        ]);
    }
}
