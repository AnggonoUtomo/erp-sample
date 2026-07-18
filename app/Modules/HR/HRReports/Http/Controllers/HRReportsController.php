<?php

namespace App\Modules\HR\HRReports\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\HRReports\Http\Requests\ListHRReportRequest;
use App\Modules\HR\HRReports\Services\ContractExpiryReportService;
use App\Modules\HR\HRReports\Services\HeadcountReportService;
use Inertia\Inertia;
use Inertia\Response;

class HRReportsController extends Controller
{
    public function __construct(
        private readonly HeadcountReportService $headcount,
        private readonly ContractExpiryReportService $contractExpiry,
    ) {}

    public function index(ListHRReportRequest $request): Response
    {
        $headcountFilters = $request->toHeadcountFilters();
        $contractExpiryFilters = $request->toContractExpiryFilters();

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
                'as_of' => $headcountFilters->asOfDate()->toDateString(),
                'employment_status_id' => $headcountFilters->employmentStatusIds[0] ?? null,
                'contract_within_days' => $contractExpiryFilters->withinDays,
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
                'byDepartement' => $this->headcount->byDepartement($headcountFilters),
                'byWorkLocation' => $this->headcount->byWorkLocation($headcountFilters),
                'byEmploymentStatus' => $this->headcount->byEmploymentStatus($headcountFilters),
            ],
            'contractExpiry' => $this->contractExpiry->expiring($contractExpiryFilters),
        ]);
    }
}
