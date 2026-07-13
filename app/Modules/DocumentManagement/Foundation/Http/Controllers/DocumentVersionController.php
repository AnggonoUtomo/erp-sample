<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DocumentManagement\Foundation\Http\Requests\ReplaceDocumentVersionRequest;
use App\Modules\DocumentManagement\Foundation\Ingestion\Exceptions\DocumentIngestionDisabled;
use App\Modules\DocumentManagement\Foundation\Upload\Exceptions\UploadPolicyViolation;
use App\Modules\DocumentManagement\Foundation\Versioning\Services\DocumentVersioningService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DocumentVersionController extends Controller
{
    public function __construct(private DocumentVersioningService $versioning) {}

    public function store(ReplaceDocumentVersionRequest $request, string $reference): JsonResponse
    {
        $dto = $request->toDto($reference);
        try {
            try {
                $result = $this->versioning->replace($dto);
            } catch (DocumentIngestionDisabled) {
                return response()->json([
                    'error' => [
                        'code' => 'DMS_INGESTION_DISABLED',
                        'message' => 'Document ingestion is unavailable.',
                    ],
                ], 503);
            } catch (UploadPolicyViolation $exception) {
                throw ValidationException::withMessages(['file' => $exception->getMessage()]);
            } catch (DomainException $exception) {
                return response()->json([
                    'error' => [
                        'code' => 'VERSION_CONFLICT',
                        'message' => $exception->getMessage(),
                    ],
                ], 409);
            }
        } finally {
            if (is_resource($dto->stream)) {
                fclose($dto->stream);
            }
        }

        return response()->json(['data' => $result->toArray()], 201);
    }
}
