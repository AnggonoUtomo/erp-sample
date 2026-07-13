<?php

namespace App\Modules\HR\EmployeeDocuments\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeDocuments\DTO\EmployeeDocumentData;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Support\EmployeeDocumentTypeCatalog;
use App\Modules\HR\EmployeeDocuments\Transactions\EmployeeDocumentsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Carbon\CarbonImmutable;
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
        private EmployeeDocumentExpiryService $expiry,
    ) {}

    public function pageData(array $filters = []): array
    {
        $employee = (int) ($filters['employee'] ?? 0);
        $documentType = (int) ($filters['document_type'] ?? 0);
        $perPage = in_array((int) ($filters['per_page'] ?? 15), [5, 10, 15, 25, 50], true)
            ? (int) ($filters['per_page'] ?? 15) : 15;
        $status = in_array(($filters['status'] ?? ''), ['PENDING', 'VERIFIED', 'REJECTED'], true)
            ? $filters['status'] : '';
        $asOf = $this->date((string) ($filters['as_of'] ?? '')) ?? CarbonImmutable::today();
        $warningDays = max(0, min((int) ($filters['warning_days'] ?? 30), 3650));
        $expiryState = in_array(($filters['expiry_state'] ?? ''), EmployeeDocumentExpiryService::STATES, true)
            ? $filters['expiry_state'] : '';
        $archiveFilter = (string) ($filters['archive'] ?? 'active');
        $archive = in_array($archiveFilter, ['active', 'with-trashed', 'only-trashed'], true)
            ? $archiveFilter : 'active';

        $query = EmployeeDocument::query()
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->with(['employee:id,employee_number,display_name', 'documentType:id,code,name'])
            ->when($employee > 0, fn (Builder $query) => $query->where('employee_id', $employee))
            ->when($documentType > 0, fn (Builder $query) => $query->where('document_type_id', $documentType))
            ->when($status !== '', fn (Builder $query) => $query->where('verification_status', $status));
        if ($expiryState !== '') {
            $this->expiry->applyState($query, $expiryState, $asOf, $warningDays);
        }

        $documents = $query
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
                'expiry_state' => $this->expiry->state($document->expires_at, $asOf, $warningDays),
                'verification_status' => $document->verification_status,
                'archived' => $document->trashed(),
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
            'filters' => [
                'employee' => $employee ?: '', 'document_type' => $documentType ?: '', 'status' => $status,
                'as_of' => $asOf->toDateString(), 'warning_days' => $warningDays, 'expiry_state' => $expiryState,
                'archive' => $archive,
            ],
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

    public function verify(EmployeeDocument $document, User $actor, ?string $reason): EmployeeDocument
    {
        return $this->transitionVerification($document, $actor, ['PENDING'], 'VERIFIED', $reason, 'verified');
    }

    public function reject(EmployeeDocument $document, User $actor, ?string $reason): EmployeeDocument
    {
        return $this->transitionVerification($document, $actor, ['PENDING'], 'REJECTED', $reason, 'rejected');
    }

    public function resubmit(EmployeeDocument $document, User $actor): EmployeeDocument
    {
        return $this->transitionVerification($document, $actor, ['VERIFIED', 'REJECTED'], 'PENDING', null, 'resubmitted');
    }

    public function archive(EmployeeDocument $document, User $actor): void
    {
        $this->transaction->run(function () use ($document, $actor): void {
            $locked = EmployeeDocument::query()->lockForUpdate()->findOrFail($document->id);
            $locked->document_number_uniqueness_key = null;
            $locked->save();
            $locked->delete();
            $this->audit->record(
                module: 'hr.employee-documents', event: 'EmployeeDocument.archived', auditable: $locked,
                description: "Archived employee document #{$locked->id}",
                oldValues: ['deleted_at' => null], newValues: ['deleted_at' => $locked->deleted_at?->toISOString()], actor: $actor,
            );
        });
    }

    public function restore(EmployeeDocument $document, User $actor): void
    {
        try {
            $this->transaction->run(function () use ($document, $actor): void {
                $locked = EmployeeDocument::withTrashed()->lockForUpdate()->findOrFail($document->id);
                if (! $locked->trashed()) {
                    throw ValidationException::withMessages(['archive' => 'Metadata dokumen tidak sedang diarsipkan.']);
                }
                if (! $locked->employee()->where('active', true)->exists()) {
                    throw ValidationException::withMessages(['employee_id' => 'Employee tidak aktif atau tidak tersedia.']);
                }
                $type = ReferenceData::query()->whereKey($locked->document_type_id)
                    ->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('active', true)->first();
                if (! $type) {
                    throw ValidationException::withMessages(['document_type_id' => 'Tipe dokumen tidak aktif atau tidak tersedia.']);
                }

                $fingerprint = $locked->document_number ? $this->fingerprint($locked->document_number) : null;
                $uniquenessKey = $fingerprint ? $this->uniquenessKey($type, $locked->employee_id, $fingerprint) : null;
                if ($uniquenessKey && EmployeeDocument::query()->whereKeyNot($locked->id)
                    ->where('document_number_uniqueness_key', $uniquenessKey)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['document_number' => 'Nomor dokumen sudah digunakan sesuai aturan tipe dokumen.']);
                }

                $archivedAt = $locked->deleted_at?->toISOString();
                $locked->document_number_fingerprint = $fingerprint;
                $locked->document_number_uniqueness_key = $uniquenessKey;
                $locked->restore();
                $this->audit->record(
                    module: 'hr.employee-documents', event: 'EmployeeDocument.restored', auditable: $locked,
                    description: "Restored employee document #{$locked->id}",
                    oldValues: ['deleted_at' => $archivedAt], newValues: ['deleted_at' => null], actor: $actor,
                );
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['19', '23000'], true)) {
                throw ValidationException::withMessages(['document_number' => 'Nomor dokumen sudah digunakan sesuai aturan tipe dokumen.']);
            }
            throw $exception;
        }
    }

    private function transitionVerification(
        EmployeeDocument $document,
        User $actor,
        array $allowedFrom,
        string $status,
        ?string $reason,
        string $event,
    ): EmployeeDocument {
        return $this->transaction->run(function () use ($document, $actor, $allowedFrom, $status, $reason, $event): EmployeeDocument {
            $locked = EmployeeDocument::query()->lockForUpdate()->findOrFail($document->id);
            if (! in_array($locked->verification_status, $allowedFrom, true)) {
                throw ValidationException::withMessages([
                    'status' => "Dokumen berstatus {$locked->verification_status} tidak dapat {$event}.",
                ]);
            }

            $oldStatus = $locked->verification_status;
            $reviewed = $status !== 'PENDING';
            $locked->update([
                'verification_status' => $status,
                'verified_by' => $reviewed ? $actor->id : null,
                'verified_at' => $reviewed ? now() : null,
                'verification_reason' => $reviewed ? $reason : null,
            ]);
            $this->audit->record(
                module: 'hr.employee-documents', event: "EmployeeDocument.{$event}", auditable: $locked,
                description: ucfirst($event)." employee document #{$locked->id}",
                oldValues: ['verification_status' => $oldStatus],
                newValues: [
                    'verification_status' => $status,
                    'verified_by' => $locked->verified_by,
                    'verified_at' => $locked->verified_at?->toISOString(),
                    'verification_reason' => $locked->verification_reason,
                ],
                actor: $actor,
            );

            return $locked->refresh();
        });
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

    private function date(string $date): ?CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $date);

            return $parsed->format('Y-m-d') === $date ? $parsed : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
