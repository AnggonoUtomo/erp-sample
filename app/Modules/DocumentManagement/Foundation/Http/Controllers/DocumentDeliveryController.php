<?php

namespace App\Modules\DocumentManagement\Foundation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\DocumentManagement\Foundation\Delivery\Exceptions\DocumentDeliveryDenied;
use App\Modules\DocumentManagement\Foundation\Delivery\Services\DocumentDeliveryService;
use App\Modules\DocumentManagement\Foundation\Http\Requests\ConsumeDocumentDeliveryRequest;
use App\Modules\DocumentManagement\Foundation\Http\Requests\IssueDocumentDeliveryRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDeliveryController extends Controller
{
    public function __construct(private DocumentDeliveryService $delivery) {}

    public function issue(IssueDocumentDeliveryRequest $request, string $reference): JsonResponse
    {
        try {
            $handoff = $this->delivery->issue($request->toDto($reference));
        } catch (DocumentDeliveryDenied) {
            return $this->denied();
        }

        return response()->json(['data' => $handoff->toArray()], 201);
    }

    public function consume(ConsumeDocumentDeliveryRequest $request): JsonResponse|StreamedResponse
    {
        try {
            $payload = $this->delivery->consume(
                $request->string('token')->toString(),
                'user:'.$request->user()->getAuthIdentifier(),
                'DOWNLOAD',
            );
        } catch (DocumentDeliveryDenied) {
            return $this->denied();
        }

        return response()->streamDownload(function () use ($payload): void {
            try {
                while (! feof($payload->stream)) {
                    $chunk = fread($payload->stream, 8192);
                    if ($chunk === false) {
                        throw new \RuntimeException('Unable to read the private document stream.');
                    }
                    echo $chunk;
                }
            } finally {
                fclose($payload->stream);
            }
        }, $payload->filename, [
            'Content-Type' => $payload->mediaType,
            'Content-Length' => (string) $payload->byteSize,
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ], 'attachment');
    }

    private function denied(): JsonResponse
    {
        return response()->json([
            'error' => ['code' => 'DELIVERY_DENIED', 'message' => 'Document delivery was denied.'],
        ], 403);
    }
}
