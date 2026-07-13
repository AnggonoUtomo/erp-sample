<?php

namespace App\Modules\HR\EmployeeDocuments\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeDocuments\DTO\EmployeeDocumentData;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Support\EmployeeDocumentTypeCatalog;
use App\Modules\HR\EmployeeDocuments\Transactions\EmployeeDocumentsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class EmployeeDocumentsService
{
    private const AUDIT_FIELDS = [
        'employee_id', 'document_type_id', 'issuer', 'issued_at', 'expires_at',
        'verification_status', 'notes',
    ];

    public function __construct(
        private EmployeeDocumentsTransaction $transaction,
        private AuditLogService $audit,
        private EmployeeDocumentTypeCatalog $types,
    ) {}

    public function pageData(array $filters = []): array
    {
        $employee = (int) ($filters['employee'] ?? 0);
        $documentType = (int) ($filters['document_type'] ?? 0);
        $perPage = in_array((int) ($filters['per_page'] ?? 15), [5, 10, 15, 25, 50], true)
            ? (int) ($filters['per_page'] ?? 15) : 15;
        $status = in_array(($filters['status'] ?? ''), ['PENDING', 'VERIFIED', 'REJECTED'], true)
            ? $filters['status'] : '';

        $documents = EmployeeDocument::query()
            ->with(['employee:id,employee_number,display_name', 'documentType:id,code,name'])
            ->when($employee > 0, fn (Builder $query) => $query->where('employee_id', $employee))
            ->when($documentType > 0, fn (Builder $query) => $query->where('document_type_id', $documentType))
            ->when($status !== '', fn (Builder $query) => $query->where('verification_status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (EmployeeDocument $document): array => [
                'id' => $document->id,
                'employee' => $document->employee->only(['id', 'employee_number', 'display_name']),
                'document_type' => $document->documentType->only(['id', 'code', 'name']),
                'document_number_masked' => $this->mask($document->document_number),
                'issuer' => $document->issuer,
                'issued_at' => $document->issued_at?->toDateString(),
                'expires_at' => $document->expires_at?->toDateString(),
                'verification_status' => $document->verification_status,
                'notes' => $document->notes,
                'created_at' => $document->created_at?->toISOString(),
            ]);

        return [
            'documents' => $documents,
            'options' => [
                'employees' => Employee::query()->where('active', true)->orderBy('display_name')->get()
                    ->map(fn (Employee $item) => ['value' => $item->id, 'label' => "{$item->display_name} ({$item->employee_number})"]),
                'documentTypes' => $this->types->inputOptions()
                    ->map(fn (ReferenceData $item) => [
                        'value' => $item->id, 'label' => $item->name,
                        'requires_expiry' => (bool) $item->metadata['requires_expiry'],
                        'requires_number' => (bool) $item->metadata['requires_number'],
                    ]),
            ],
            'filters' => ['employee' => $employee ?: '', 'document_type' => $documentType ?: '', 'status' => $status],
        ];
    }

    public function create(EmployeeDocumentData $data): EmployeeDocument
    {
        try {
            return $this->transaction->run(function () use ($data): EmployeeDocument {
                $type = ReferenceData::query()->whereKey($data->documentTypeId)
                    ->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('active', true)->lockForUpdate()->firstOrFail();
                $fingerprint = $data->documentNumber ? $this->fingerprint($data->documentNumber) : null;
                $uniquenessKey = $fingerprint ? $this->uniquenessKey($type, $data->employeeId, $fingerprint) : null;
                if ($uniquenessKey && EmployeeDocument::query()->where('document_number_uniqueness_key', $uniquenessKey)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['document_number' => 'Nomor dokumen sudah digunakan sesuai aturan tipe dokumen.']);
                }

                $document = EmployeeDocument::query()->create([
                    'employee_id' => $data->employeeId,
                    'document_type_id' => $data->documentTypeId,
                    'document_number' => $data->documentNumber,
                    'document_number_fingerprint' => $fingerprint,
                    'document_number_uniqueness_key' => $uniquenessKey,
                    'issuer' => $data->issuer,
                    'issued_at' => $data->issuedAt,
                    'expires_at' => $data->expiresAt,
                    'verification_status' => 'PENDING',
                    'notes' => $data->notes,
                ]);
                $this->audit->record(
                    module: 'hr.employee-documents', event: 'EmployeeDocument.created', auditable: $document,
                    description: "Created {$type->code} metadata for employee #{$data->employeeId}",
                    newValues: $document->only(self::AUDIT_FIELDS),
                );

                return $document;
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['19', '23000'], true)) {
                throw ValidationException::withMessages(['document_number' => 'Nomor dokumen sudah digunakan sesuai aturan tipe dokumen.']);
            }
            throw $exception;
        }
    }

    private function fingerprint(string $number): string
    {
        $normalized = preg_replace('/[^A-Z0-9]/', '', strtoupper($number)) ?? '';

        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }

    private function uniquenessKey(ReferenceData $type, int $employeeId, string $fingerprint): ?string
    {
        $scope = $type->metadata['number_unique_scope'];
        if ($scope === 'NONE') {
            return null;
        }
        $context = $scope === 'GLOBAL'
            ? "GLOBAL|{$type->id}|{$fingerprint}"
            : "EMPLOYEE|{$employeeId}|{$type->id}|{$fingerprint}";

        return hash_hmac('sha256', $context, (string) config('app.key'));
    }

    private function mask(?string $number): ?string
    {
        if (! $number) {
            return null;
        }
        $visible = mb_substr($number, -4);

        return str_repeat('*', max(0, mb_strlen($number) - mb_strlen($visible))).$visible;
    }
}
