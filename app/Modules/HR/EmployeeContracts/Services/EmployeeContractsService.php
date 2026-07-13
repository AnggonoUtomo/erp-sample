<?php

namespace App\Modules\HR\EmployeeContracts\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\DTO\EmployeeContractData;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Transactions\EmployeeContractsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeContractsService
{
    public function __construct(
        private EmployeeContractsTransaction $transaction,
        private AuditLogService $audit,
        private EmployeeContractExpiryService $expiry,
    ) {}

    public function pageData(string $archive = 'active', string $expiryDate = '', int $expiryWithin = 30): array
    {
        $archive = in_array($archive, ['active', 'with-trashed', 'only-trashed'], true) ? $archive : 'active';
        $expiryDate = $this->validDate($expiryDate) ? $expiryDate : '';
        $expiryWithin = max(0, min($expiryWithin, 3650));

        $contracts = EmployeeContract::query()
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed());
        if ($expiryDate !== '') {
            $this->expiry->applyWindow($contracts, CarbonImmutable::createFromFormat('!Y-m-d', $expiryDate), $expiryWithin);
        }

        return [
            'contracts' => $contracts->with(['employee', 'employmentType'])->latest('start_date')->paginate(15)->withQueryString()->through(fn ($contract) => [
                'id' => $contract->id, 'employee_id' => $contract->employee_id, 'employment_type_id' => $contract->employment_type_id,
                'contract_number' => $contract->contract_number, 'start_date' => $contract->start_date->toDateString(),
                'end_date' => $contract->end_date?->toDateString(), 'status' => $contract->status, 'ended_reason' => $contract->ended_reason,
                'superseded_by_id' => $contract->superseded_by_id, 'archived' => $contract->trashed(), 'notes' => $contract->notes,
                'employee' => ['id' => $contract->employee->id, 'display_name' => $contract->employee->display_name],
                'employment_type' => ['id' => $contract->employmentType->id, 'name' => $contract->employmentType->name],
            ]),
            'options' => [
                'employees' => Employee::query()->where('active', true)->orderBy('display_name')->get()->map(fn ($item) => ['value' => $item->id, 'label' => "{$item->display_name} ({$item->employee_number})"]),
                'employmentTypes' => EmploymentType::query()->where('active', true)->orderBy('name')->get()->map(fn ($item) => ['value' => $item->id, 'label' => $item->name]),
            ],
            'filters' => ['archive' => $archive, 'expiry_date' => $expiryDate, 'expiry_within' => $expiryWithin],
        ];
    }

    public function create(EmployeeContractData $data): EmployeeContract
    {
        return $this->transaction->run(function () use ($data) {
            $overlaps = EmployeeContract::query()
                ->forEmployee($data->employeeId)
                ->overlapping($data->startDate, $data->endDate)
                ->lockForUpdate()
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages(['start_date' => 'Periode kontrak overlap dengan kontrak lain.']);
            }

            $contract = EmployeeContract::query()->create([
                'employee_id' => $data->employeeId, 'employment_type_id' => $data->employmentTypeId,
                'contract_number' => $data->contractNumber, 'start_date' => $data->startDate, 'end_date' => $data->endDate,
                'probation_end_date' => $data->probationEndDate, 'signed_date' => $data->signedDate, 'notes' => $data->notes, 'status' => 'DRAFT',
            ]);
            $this->audit->record(module: 'hr.employee-contracts', event: 'EmployeeContract.created', auditable: $contract, description: "Created contract {$contract->contract_number}", newValues: $contract->toArray());

            return $contract;
        });
    }

    public function activate(EmployeeContract $contract): EmployeeContract
    {
        return $this->transaction->run(function () use ($contract) {
            $locked = EmployeeContract::query()->lockForUpdate()->findOrFail($contract->id);

            if ($locked->status === 'ACTIVE') {
                return $locked;
            }

            if ($locked->status !== 'DRAFT') {
                throw ValidationException::withMessages(['status' => 'Hanya draft contract yang dapat diaktifkan.']);
            }

            $overlaps = EmployeeContract::query()
                ->forEmployee($locked->employee_id)
                ->overlapping($locked->start_date->toDateString(), $locked->end_date?->toDateString())
                ->whereKeyNot($locked->id)
                ->lockForUpdate()
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages(['start_date' => 'Contract tidak dapat diaktifkan karena periodenya overlap.']);
            }

            $locked->update(['status' => 'ACTIVE']);
            $this->audit->record(
                module: 'hr.employee-contracts',
                event: 'EmployeeContract.activated',
                auditable: $locked,
                description: "Activated contract {$locked->contract_number}",
                oldValues: ['status' => 'DRAFT'],
                newValues: ['status' => 'ACTIVE'],
            );

            return $locked->refresh();
        });
    }

    public function terminate(EmployeeContract $contract, string $endDate, string $reason): EmployeeContract
    {
        return $this->transition($contract, ['ACTIVE'], 'ENDED', $reason, ['end_date' => $endDate], 'terminated');
    }

    public function cancel(EmployeeContract $contract, string $reason): EmployeeContract
    {
        return $this->transition($contract, ['DRAFT', 'ACTIVE'], 'CANCELLED', $reason, [], 'cancelled');
    }

    private function transition(EmployeeContract $contract, array $allowedFrom, string $status, string $reason, array $values, string $event): EmployeeContract
    {
        return $this->transaction->run(function () use ($contract, $allowedFrom, $status, $reason, $values, $event) {
            $locked = EmployeeContract::query()->lockForUpdate()->findOrFail($contract->id);
            if (! in_array($locked->status, $allowedFrom, true)) {
                throw ValidationException::withMessages(['status' => "Contract berstatus {$locked->status} tidak dapat {$event}."]);
            }

            $oldStatus = $locked->status;
            $locked->update([...$values, 'status' => $status, 'ended_reason' => $reason]);
            $this->audit->record(
                module: 'hr.employee-contracts', event: "EmployeeContract.{$event}", auditable: $locked,
                description: ucfirst($event)." contract {$locked->contract_number}",
                oldValues: ['status' => $oldStatus], newValues: ['status' => $status, 'end_date' => $locked->end_date?->toDateString(), 'reason' => $reason],
            );

            return $locked->refresh();
        });
    }

    public function supersede(EmployeeContract $contract, array $data): EmployeeContract
    {
        return $this->transaction->run(function () use ($contract, $data) {
            $old = EmployeeContract::query()->lockForUpdate()->findOrFail($contract->id);
            if ($old->status !== 'ACTIVE' || $old->superseded_by_id) {
                throw ValidationException::withMessages(['status' => 'Hanya active contract yang belum digantikan dapat disupersede.']);
            }

            $replacementStart = Carbon::parse($data['start_date'])->startOfDay();
            if ($replacementStart->lte($old->start_date)) {
                throw ValidationException::withMessages(['start_date' => 'Replacement harus dimulai setelah contract lama.']);
            }

            $overlaps = EmployeeContract::query()->forEmployee($old->employee_id)
                ->overlapping($replacementStart->toDateString(), $data['end_date'] ?? null)
                ->whereKeyNot($old->id)->lockForUpdate()->exists();
            if ($overlaps) {
                throw ValidationException::withMessages(['start_date' => 'Periode replacement overlap dengan contract lain.']);
            }

            $replacement = EmployeeContract::query()->create([
                'employee_id' => $old->employee_id, 'employment_type_id' => $data['employment_type_id'],
                'contract_number' => strtoupper(trim($data['contract_number'])), 'start_date' => $replacementStart,
                'end_date' => $data['end_date'] ?? null, 'probation_end_date' => $data['probation_end_date'] ?? null,
                'signed_date' => $data['signed_date'] ?? null, 'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
                'status' => 'ACTIVE',
            ]);
            $old->update([
                'status' => 'ENDED', 'end_date' => $replacementStart->copy()->subDay(),
                'ended_reason' => trim($data['reason']), 'superseded_by_id' => $replacement->id,
            ]);
            $this->audit->record(
                module: 'hr.employee-contracts', event: 'EmployeeContract.superseded', auditable: $old,
                description: "Superseded contract {$old->contract_number} with {$replacement->contract_number}",
                oldValues: ['status' => 'ACTIVE'],
                newValues: ['status' => 'ENDED', 'end_date' => $old->end_date?->toDateString(), 'superseded_by_id' => $replacement->id],
            );

            return $replacement->refresh();
        });
    }

    public function archive(EmployeeContract $contract): void
    {
        $this->transaction->run(function () use ($contract): void {
            $locked = EmployeeContract::query()->lockForUpdate()->findOrFail($contract->id);
            $locked->delete();
            $this->audit->record(
                module: 'hr.employee-contracts', event: 'EmployeeContract.archived', auditable: $locked,
                description: "Archived contract {$locked->contract_number}", oldValues: ['deleted_at' => null],
                newValues: ['deleted_at' => $locked->deleted_at?->toISOString()],
            );
        });
    }

    public function restore(EmployeeContract $contract): void
    {
        $this->transaction->run(function () use ($contract): void {
            $locked = EmployeeContract::withTrashed()->lockForUpdate()->findOrFail($contract->id);
            if (! $locked->trashed()) {
                return;
            }

            $duplicateNumber = EmployeeContract::withTrashed()->where('contract_number', $locked->contract_number)
                ->whereKeyNot($locked->id)->lockForUpdate()->exists();
            if ($duplicateNumber) {
                throw ValidationException::withMessages(['contract_number' => 'Nomor contract sudah digunakan.']);
            }

            $overlaps = EmployeeContract::query()->forEmployee($locked->employee_id)
                ->overlapping($locked->start_date->toDateString(), $locked->end_date?->toDateString())
                ->whereKeyNot($locked->id)->lockForUpdate()->exists();
            if ($overlaps) {
                throw ValidationException::withMessages(['start_date' => 'Contract tidak dapat dipulihkan karena periodenya overlap.']);
            }

            $archivedAt = $locked->deleted_at?->toISOString();
            $locked->restore();
            $this->audit->record(
                module: 'hr.employee-contracts', event: 'EmployeeContract.restored', auditable: $locked,
                description: "Restored contract {$locked->contract_number}", oldValues: ['deleted_at' => $archivedAt],
                newValues: ['deleted_at' => null],
            );
        });
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
