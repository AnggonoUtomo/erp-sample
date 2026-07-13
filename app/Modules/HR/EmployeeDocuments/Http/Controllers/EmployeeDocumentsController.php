<?php

namespace App\Modules\HR\EmployeeDocuments\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HR\EmployeeDocuments\Http\Requests\EmployeeDocumentAttachmentRequest;
use App\Modules\HR\EmployeeDocuments\Http\Requests\EmployeeDocumentVerificationRequest;
use App\Modules\HR\EmployeeDocuments\Http\Requests\StoreEmployeeDocumentRequest;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAccessDenied;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentAccessService;
use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentAttachmentService;
use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeDocumentsController extends Controller
{
    public function __construct(
        private EmployeeDocumentsService $documents,
        private EmployeeDocumentAttachmentService $attachments,
        private EmployeeDocumentAccessService $access,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        return Inertia::render('hr/employee-documents/index', $this->documents->pageData($request->only([
            'employee', 'document_type', 'status', 'per_page', 'as_of', 'warning_days', 'expiry_state', 'archive',
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

    public function destroy(Request $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->authorize('delete', $employeeDocument);
        $this->documents->archive($employeeDocument, $request->user());

        return back()->with('success', 'Metadata dokumen berhasil diarsipkan.');
    }

    public function restore(Request $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->authorize('restore', $employeeDocument);
        $this->documents->restore($employeeDocument, $request->user());

        return back()->with('success', 'Metadata dokumen berhasil dipulihkan.');
    }

    public function attach(EmployeeDocumentAttachmentRequest $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->attachments->attach($employeeDocument, $request->toDto($employeeDocument), $request->user());

        return back()->with('success', 'File berhasil dihubungkan ke metadata employee.');
    }

    public function detach(Request $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $this->authorize('attach', $employeeDocument);
        $this->attachments->detach($employeeDocument, $request->user());

        return back()->with('success', 'Reference file berhasil dilepas tanpa menghapus file DMS.');
    }

    public function delivery(Request $request, EmployeeDocument $employeeDocument): JsonResponse
    {
        $this->authorize('accessAttachment', $employeeDocument);

        try {
            $handoff = $this->access->issueDownload(
                $employeeDocument,
                'user:'.$request->user()->getAuthIdentifier(),
            );
        } catch (EmployeeDocumentAccessDenied) {
            return response()->json([
                'error' => ['code' => 'ATTACHMENT_ACCESS_DENIED', 'message' => 'Attachment access was denied.'],
            ], 403);
        }

        return response()->json(['data' => $handoff->toArray()], 201);
    }
}
