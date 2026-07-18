<?php

namespace App\Modules\HR\HRReports\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRReportsController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(
            $request->user()?->canAny(['hr-reports.view', 'hr-reports.manage']) ?? false,
            403,
        );

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
        ]);
    }
}
