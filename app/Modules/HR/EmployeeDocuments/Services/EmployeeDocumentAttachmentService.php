<?php

namespace App\Modules\HR\EmployeeDocuments\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeDocuments\Integration\Contracts\EmployeeDocumentAttachmentGateway;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAttachmentUnavailable;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Transactions\EmployeeDocumentsTransaction;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentAttachmentService
{
    public function __construct(
        private EmployeeDocumentAttachmentGateway $gateway,
        private EmployeeDocumentsTransaction $transaction,
        private AuditLogService $audit,
    ) {}

    public function attach(EmployeeDocument $document, AttachEmployeeDocumentV1 $request, User $actor): EmployeeDocument
    {
        $keyHash = hash_hmac('sha256', $request->idempotencyKey, (string) config('app.key'));
        $this->reserve($document, $keyHash);

        try {
            $reference = $this->gateway->createFor($request);
        } catch (EmployeeDocumentAttachmentUnavailable) {
            throw ValidationException::withMessages([
                'attachment' => 'Document Management belum dapat menyimpan file. Gunakan file dan idempotency key yang sama untuk retry.',
            ]);
        } finally {
            if (is_resource($request->stream)) {
                fclose($request->stream);
            }
        }

        return $this->transaction->run(function () use ($document, $keyHash, $reference, $actor): EmployeeDocument {
            $locked = EmployeeDocument::query()->lockForUpdate()->findOrFail($document->getKey());
            if (! hash_equals((string) $locked->attachment_idempotency_key_hash, $keyHash)) {
                throw ValidationException::withMessages(['idempotency_key' => 'Operasi attachment telah berubah. Muat ulang halaman.']);
            }
            if ($locked->document_reference !== null && $locked->document_reference !== $reference->value()) {
                throw ValidationException::withMessages(['attachment' => 'Metadata sudah memiliki attachment berbeda.']);
            }

            $wasAttached = $locked->document_reference !== null;
            $locked->document_reference = $reference->value();
            $locked->document_reference_version = 1;
            $locked->save();
            if (! $wasAttached) {
                $this->audit->record(
                    module: 'hr.employee-documents',
                    event: 'EmployeeDocument.attachment_attached',
                    auditable: $locked,
                    description: "Attached DMS document to employee document #{$locked->getKey()}",
                    newValues: ['attached' => true, 'reference_schema_version' => 1],
                    actor: $actor,
                );
            }

            return $locked;
        });
    }

    public function detach(EmployeeDocument $document, User $actor): EmployeeDocument
    {
        return $this->transaction->run(function () use ($document, $actor): EmployeeDocument {
            $locked = EmployeeDocument::query()->lockForUpdate()->findOrFail($document->getKey());
            if ($locked->document_reference === null) {
                return $locked;
            }

            $locked->document_reference = null;
            $locked->document_reference_version = null;
            $locked->attachment_idempotency_key_hash = null;
            $locked->save();
            $this->audit->record(
                module: 'hr.employee-documents',
                event: 'EmployeeDocument.attachment_detached',
                auditable: $locked,
                description: "Detached DMS reference from employee document #{$locked->getKey()}",
                oldValues: ['attached' => true, 'reference_schema_version' => 1],
                newValues: ['attached' => false],
                actor: $actor,
            );

            return $locked;
        });
    }

    private function reserve(EmployeeDocument $document, string $keyHash): void
    {
        $this->transaction->run(function () use ($document, $keyHash): void {
            $locked = EmployeeDocument::query()->lockForUpdate()->findOrFail($document->getKey());
            if ($locked->attachment_idempotency_key_hash !== null
                && ! hash_equals($locked->attachment_idempotency_key_hash, $keyHash)) {
                throw ValidationException::withMessages([
                    'idempotency_key' => 'Attachment sedang atau sudah diproses dengan idempotency key berbeda.',
                ]);
            }
            if ($locked->attachment_idempotency_key_hash === null) {
                $locked->update(['attachment_idempotency_key_hash' => $keyHash]);
            }
        });
    }
}
