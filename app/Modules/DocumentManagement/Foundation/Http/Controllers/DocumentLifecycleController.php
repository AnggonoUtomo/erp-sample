<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DocumentManagement\Foundation\Http\Requests\ArchiveDocumentRequest;
use App\Modules\DocumentManagement\Foundation\Http\Requests\RestoreDocumentRequest;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Lifecycle\Services\DocumentLifecycleService;
use DomainException;
use Illuminate\Http\JsonResponse;

class DocumentLifecycleController extends Controller
{
    public function __construct(private DocumentLifecycleService $lifecycle) {}

    public function archive(ArchiveDocumentRequest $request, string $reference): JsonResponse
    {
        try {
            $descriptor = $this->lifecycle->archive(
                new DocumentReferenceV1($reference),
                'user:'.$request->user()->getAuthIdentifier(),
                $request->string('reason')->toString(),
            );
        } catch (DomainException $exception) {
            return $this->conflict($exception);
        }

        return response()->json(['data' => $descriptor->toArray()]);
    }

    public function restore(RestoreDocumentRequest $request, string $reference): JsonResponse
    {
        try {
            $descriptor = $this->lifecycle->restore(
                new DocumentReferenceV1($reference),
                'user:'.$request->user()->getAuthIdentifier(),
            );
        } catch (DomainException $exception) {
            return $this->conflict($exception);
        }

        return response()->json(['data' => $descriptor->toArray()]);
    }

    private function conflict(DomainException $exception): JsonResponse
    {
        return response()->json([
            'error' => ['code' => 'DOCUMENT_LIFECYCLE_CONFLICT', 'message' => $exception->getMessage()],
        ], 409);
    }
}
