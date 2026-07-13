<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DocumentManagement\Foundation\Http\Requests\IngestDocumentRequest;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Upload\Exceptions\UploadPolicyViolation;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class DocumentIngestionController extends Controller
{
    public function __construct(private DocumentIngestionService $ingestion) {}

    public function __invoke(IngestDocumentRequest $request): JsonResponse
    {
        $dto = $request->toDto();
        try {
            try {
                $result = $this->ingestion->ingest($dto);
            } catch (UploadPolicyViolation $exception) {
                throw ValidationException::withMessages(['file' => $exception->getMessage()]);
            } catch (DomainException) {
                return response()->json([
                    'error' => [
                        'code' => 'IDEMPOTENCY_CONFLICT',
                        'message' => 'Idempotency key was already used for a different upload.',
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
