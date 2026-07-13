<?php

namespace App\Modules\HR\EmployeeDocuments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmployeeDocuments\Http\Requests\EmployeeDocumentVerificationRequest;
use App\Modules\HR\EmployeeDocuments\Http\Requests\StoreEmployeeDocumentRequest;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeDocumentsController extends Controller
{
    public function __construct(private EmployeeDocumentsService $documents) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        return Inertia::render('hr/employee-documents/index', $this->documents->pageData($request->only([
            'employee', 'document_type', 'status', 'per_page', 'as_of', 'warning_days', 'expiry_state',
        ])));
    }

    public function store(StoreEmployeeDocumentRequest $request): RedirectResponse
    {
        $this->documents->create($request->toDto());

        return back()->with('success', 'Metadata dokumen employee berhasil dibuat.');
    }

    public function verify(EmployeeDocumentVerificationRequest $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->documents->verify($employeeDocument, $request->user(), $request->reason());

        return back()->with('success', 'Metadata dokumen berhasil diverifikasi.');
    }

    public function reject(EmployeeDocumentVerificationRequest $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->documents->reject($employeeDocument, $request->user(), $request->reason());

        return back()->with('success', 'Metadata dokumen berhasil ditolak.');
    }

    public function resubmit(EmployeeDocumentVerificationRequest $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->documents->resubmit($employeeDocument, $request->user());

        return back()->with('success', 'Metadata dokumen dikembalikan ke status pending.');
    }
}
