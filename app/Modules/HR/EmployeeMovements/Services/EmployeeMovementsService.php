<?php

namespace App\Modules\HR\EmployeeMovements\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractEmploymentTypeGuard;
use App\Modules\HR\EmployeeMovements\DTO\EmployeeMovementData;
use App\Modules\HR\EmployeeMovements\Integration\Events\EmployeeMovementAppliedV1;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\EmployeeMovements\Transactions\EmployeeMovementsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationException as LaravelValidationException;

class EmployeeMovementsService
{
    private const PROFILE_FIELDS = ['departement_id', 'position_id', 'job_level_id', 'employment_status_id', 'employment_type_id', 'work_location_id', 'supervisor_id'];

    public function __construct(
        private EmployeeMovementsTransaction $transaction,
        private AuditLogService $audit,
        private EmployeeContractEmploymentTypeGuard $contractEmploymentTypeGuard,
    ) {}

    public function pageData(): array
    {
        $departments = Departement::query()->where('active', true)->orderBy('name')->get(['id', 'name']);
        $positions = Position::query()->where('active', true)->orderBy('name')->get(['id', 'departement_id', 'name']);
        $jobLevels = JobLevel::query()->where('active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $employmentStatuses = EmploymentStatus::query()->where('active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $employmentTypes = EmploymentType::query()->where('active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);
        $locations = WorkLocation::query()->where('active', true)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::query()->where('active', true)->orderBy('display_name')->get(['id', 'employee_number', 'display_name']);
        $labels = [
            'departement_id' => $departments->pluck('name', 'id'),
            'position_id' => $positions->pluck('name', 'id'),
            'job_level_id' => $jobLevels->pluck('name', 'id'),
            'employment_status_id' => $employmentStatuses->pluck('name', 'id'),
            'employment_type_id' => $employmentTypes->pluck('name', 'id'),
            'work_location_id' => $locations->pluck('name', 'id'),
            'supervisor_id' => $employees->pluck('display_name', 'id'),
        ];

        return [
            'movements' => EmployeeMovement::query()->with(['employee:id,display_name,employee_number', 'creator:id,name', 'approver:id,name', 'applier:id,name'])
                ->latest()->paginate(15)->through(function (EmployeeMovement $movement) use ($labels): array {
                    return [
                        'id' => $movement->id, 'type' => $movement->type, 'effective_date' => $movement->effective_date->toDateString(),
                        'status' => $movement->status, 'reason' => $movement->reason, 'notes' => $movement->notes,
                        'employee' => $movement->employee->only(['id', 'display_name', 'employee_number']),
                        'before' => $this->labelSnapshot($movement->before_values, $labels),
                        'after' => $this->labelSnapshot($movement->after_values, $labels),
                        'creator' => $movement->creator?->only(['id', 'name']),
                        'approver' => $movement->approver?->only(['id', 'name']),
                        'applier' => $movement->applier?->only(['id', 'name']),
                        'approved_at' => $movement->approved_at?->toISOString(),
                        'applied_at' => $movement->applied_at?->toISOString(),
                        'cancelled_at' => $movement->cancelled_at?->toISOString(),
                        'cancel_reason' => $movement->cancel_reason,
                    ];
                }),
            'options' => [
                'today' => now()->toDateString(),
                'employees' => $employees->map(fn (Employee $employee) => ['value' => $employee->id, 'label' => "{$employee->display_name} ({$employee->employee_number})"]),
                'departments' => $departments->map(fn (Departement $item) => ['value' => $item->id, 'label' => $item->name]),
                'positions' => $positions->map(fn (Position $item) => ['value' => $item->id, 'departement_id' => $item->departement_id, 'label' => $item->name]),
                'jobLevels' => $jobLevels->map(fn (JobLevel $item) => ['value' => $item->id, 'label' => $item->name]),
                'employmentStatuses' => $employmentStatuses->map(fn (EmploymentStatus $item) => ['value' => $item->id, 'label' => $item->name]),
                'employmentTypes' => $employmentTypes->map(fn (EmploymentType $item) => ['value' => $item->id, 'label' => $item->name]),
                'locations' => $locations->map(fn (WorkLocation $item) => ['value' => $item->id, 'label' => $item->name]),
                'supervisors' => $employees->map(fn (Employee $employee) => ['value' => $employee->id, 'label' => $employee->display_name]),
            ],
        ];
    }

    public function create(EmployeeMovementData $data): EmployeeMovement
    {
        return $this->transaction->run(function () use ($data): EmployeeMovement {
            $employee = Employee::query()->lockForUpdate()->findOrFail($data->employeeId);
            $before = $this->snapshot($employee);
            $after = [
                'departement_id' => $data->departementId ?? $employee->departement_id,
                'position_id' => $data->positionId ?? $employee->position_id,
                'job_level_id' => $data->jobLevelId ?? $employee->job_level_id,
                'employment_status_id' => $data->employmentStatusId ?? $employee->employment_status_id,
                'employment_type_id' => $data->employmentTypeId ?? $employee->employment_type_id,
                'work_location_id' => $data->workLocationId ?? $employee->work_location_id,
                'supervisor_id' => $data->supervisorId ?? $employee->supervisor_id,
            ];
            $this->validateTarget($employee, $after, $data->effectiveDate);
            $this->validateMovementType($employee, $data->type, $before, $after);
            if ($before === $after) {
                throw ValidationException::withMessages(['movement' => 'Movement harus mengubah minimal satu assignment employee.']);
            }

            $movement = EmployeeMovement::query()->create([
                'employee_id' => $employee->id, 'type' => $data->type, 'effective_date' => $data->effectiveDate,
                'status' => 'DRAFT', 'reason' => $data->reason, 'notes' => $data->notes,
                'before_values' => $before, 'after_values' => $after, 'created_by' => auth()->id(),
            ]);
            $this->audit->record(
                module: 'hr.employee-movements', event: 'EmployeeMovement.created', auditable: $movement,
                description: "Created {$data->type} draft for {$employee->display_name}", newValues: $movement->toArray(),
            );

            return $movement;
        });
    }

    public function apply(EmployeeMovement $movement): EmployeeMovement
    {
        return $this->applyForBusinessDate($movement, now()->toDateString());
    }

    public function approve(EmployeeMovement $movement): EmployeeMovement
    {
        return $this->transaction->run(function () use ($movement): EmployeeMovement {
            $locked = EmployeeMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if ($locked->status !== 'DRAFT') {
                throw ValidationException::withMessages(['status' => 'Hanya movement DRAFT yang dapat di-approve.']);
            }

            $employee = Employee::query()->lockForUpdate()->findOrFail($locked->employee_id);
            if ($this->snapshot($employee) !== $this->normalizeSnapshot($locked->before_values)) {
                throw ValidationException::withMessages(['profile' => 'Profile employee telah berubah. Buat movement baru dari data terkini.']);
            }
            $after = $this->normalizeSnapshot($locked->after_values);
            $this->validateTarget($employee, $after, $locked->effective_date->toDateString());
            $this->validateMovementType($employee, $locked->type, $this->normalizeSnapshot($locked->before_values), $after);

            $locked->update(['status' => 'APPROVED', 'approved_by' => auth()->id(), 'approved_at' => now()]);
            $this->audit->record(
                module: 'hr.employee-movements',
                event: 'EmployeeMovement.approved',
                auditable: $locked,
                description: "Approved {$locked->type} movement for employee #{$locked->employee_id}",
                oldValues: ['status' => 'DRAFT'],
                newValues: ['status' => 'APPROVED'],
            );

            return $locked->refresh();
        });
    }

    public function cancel(EmployeeMovement $movement, string $reason): EmployeeMovement
    {
        return $this->transaction->run(function () use ($movement, $reason): EmployeeMovement {
            $locked = EmployeeMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if (! in_array($locked->status, ['DRAFT', 'APPROVED'], true)) {
                throw ValidationException::withMessages(['status' => 'Hanya movement DRAFT yang dapat dibatalkan.']);
            }

            $oldStatus = $locked->status;
            $locked->update([
                'status' => 'CANCELLED',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancel_reason' => trim($reason),
            ]);
            $this->audit->record(
                module: 'hr.employee-movements',
                event: 'EmployeeMovement.cancelled',
                auditable: $locked,
                description: "Cancelled {$locked->type} movement for employee #{$locked->employee_id}",
                oldValues: ['status' => $oldStatus],
                newValues: ['status' => 'CANCELLED', 'reason' => trim($reason)],
            );

            return $locked->refresh();
        });
    }

    /**
     * @return array{due: int, applied: int, failed: int, errors: array<int, string>}
     */
    public function applyDue(string $businessDate, bool $dryRun = false): array
    {
        $due = EmployeeMovement::query()
            ->where('status', 'APPROVED')
            ->whereDate('effective_date', '<=', $businessDate)
            ->orderBy('effective_date')
            ->orderBy('id')
            ->get();

        if ($dryRun) {
            return ['due' => $due->count(), 'applied' => 0, 'failed' => 0, 'errors' => []];
        }

        $applied = 0;
        $errors = [];
        foreach ($due as $movement) {
            try {
                $this->applyForBusinessDate($movement, $businessDate);
                $applied++;
            } catch (LaravelValidationException $exception) {
                $errors[] = "Movement {$movement->id}: ".collect($exception->errors())->flatten()->join('; ');
            }
        }

        return ['due' => $due->count(), 'applied' => $applied, 'failed' => count($errors), 'errors' => $errors];
    }

    private function applyForBusinessDate(EmployeeMovement $movement, string $businessDate): EmployeeMovement
    {
        return $this->transaction->run(function () use ($movement, $businessDate): EmployeeMovement {
            $locked = EmployeeMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if ($locked->status !== 'APPROVED') {
                throw ValidationException::withMessages(['status' => 'Hanya movement APPROVED yang dapat diterapkan.']);
            }
            if ($locked->effective_date->toDateString() > $businessDate) {
                throw ValidationException::withMessages(['effective_date' => 'Movement hanya dapat diterapkan saat sudah mencapai effective date.']);
            }

            $employee = Employee::query()->lockForUpdate()->findOrFail($locked->employee_id);
            if ($this->snapshot($employee) !== $this->normalizeSnapshot($locked->before_values)) {
                throw ValidationException::withMessages(['profile' => 'Profile employee telah berubah. Buat movement baru dari data terkini.']);
            }
            $after = $this->normalizeSnapshot($locked->after_values);
            $this->validateTarget($employee, $after, $locked->effective_date->toDateString());
            $this->validateMovementType($employee, $locked->type, $this->normalizeSnapshot($locked->before_values), $after);
            $employee->update($after);
            $locked->update(['status' => 'APPLIED', 'applied_by' => auth()->id(), 'applied_at' => now()]);
            $locked->refresh();
            $this->audit->record(
                module: 'hr.employee-movements', event: 'EmployeeMovement.applied', auditable: $locked,
                description: "Applied {$locked->type} for {$employee->display_name}",
                oldValues: $locked->before_values, newValues: $after,
            );
            event(EmployeeMovementAppliedV1::fromMovement($locked));

            return $locked;
        });
    }

    public function archive(EmployeeMovement $movement): void
    {
        $this->transaction->run(function () use ($movement): void {
            $locked = EmployeeMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if (! in_array($locked->status, ['CANCELLED'], true)) {
                throw ValidationException::withMessages(['status' => 'Hanya movement CANCELLED yang dapat di-archive.']);
            }
            $locked->update(['archived_by' => auth()->id(), 'archived_at' => now()]);
            $locked->delete();
            $this->audit->record(
                module: 'hr.employee-movements',
                event: 'EmployeeMovement.archived',
                auditable: $locked,
                description: "Archived {$locked->type} movement #{$locked->id}",
                oldValues: ['deleted_at' => null],
                newValues: ['deleted_at' => $locked->deleted_at?->toISOString()],
            );
        });
    }

    public function restore(int $movementId): void
    {
        $this->transaction->run(function () use ($movementId): void {
            $locked = EmployeeMovement::withTrashed()->lockForUpdate()->findOrFail($movementId);
            if (! $locked->trashed()) {
                return;
            }
            $archivedAt = $locked->deleted_at?->toISOString();
            $locked->restore();
            $locked->update(['archived_by' => null, 'archived_at' => null]);
            $this->audit->record(
                module: 'hr.employee-movements',
                event: 'EmployeeMovement.restored',
                auditable: $locked,
                description: "Restored {$locked->type} movement #{$locked->id}",
                oldValues: ['deleted_at' => $archivedAt],
                newValues: ['deleted_at' => null],
            );
        });
    }

    private function snapshot(Employee $employee): array
    {
        return collect(self::PROFILE_FIELDS)->mapWithKeys(fn (string $field) => [$field => $employee->getAttribute($field)])->all();
    }

    private function normalizeSnapshot(array $snapshot): array
    {
        return collect(self::PROFILE_FIELDS)->mapWithKeys(fn (string $field) => [
            $field => isset($snapshot[$field]) ? (int) $snapshot[$field] : null,
        ])->all();
    }

    private function validateTarget(Employee $employee, array $target, string $effectiveDate): void
    {
        if ($target['supervisor_id'] === $employee->id) {
            throw ValidationException::withMessages(['supervisor_id' => 'Employee tidak boleh menjadi supervisor dirinya sendiri.']);
        }
        if ($target['position_id'] && Position::query()->whereKey($target['position_id'])->where('departement_id', $target['departement_id'])->doesntExist()) {
            throw ValidationException::withMessages(['position_id' => 'Position harus berada pada department tujuan.']);
        }
        if ($target['job_level_id'] && JobLevel::query()->whereKey($target['job_level_id'])->where('active', true)->doesntExist()) {
            throw ValidationException::withMessages(['job_level_id' => 'Job level tujuan harus aktif.']);
        }
        if ($target['employment_status_id'] && EmploymentStatus::query()->whereKey($target['employment_status_id'])->where('active', true)->doesntExist()) {
            throw ValidationException::withMessages(['employment_status_id' => 'Employment status tujuan harus aktif.']);
        }
        if ($target['employment_type_id'] && EmploymentType::query()->whereKey($target['employment_type_id'])->where('active', true)->doesntExist()) {
            throw ValidationException::withMessages(['employment_type_id' => 'Employment type tujuan harus aktif.']);
        }
        if (($target['employment_type_id'] ?? null) !== $employee->employment_type_id
            && ! $this->contractEmploymentTypeGuard->hasActiveEffectiveContract($employee->id, (int) $target['employment_type_id'], $effectiveDate)) {
            throw ValidationException::withMessages(['employment_type_id' => 'Perubahan employment type wajib didukung active contract yang efektif pada tanggal movement.']);
        }
    }

    private function validateMovementType(Employee $employee, string $type, array $before, array $after): void
    {
        if (! in_array($type, ['TRANSFER', 'PROMOTION', 'DEMOTION', 'EMPLOYMENT_CHANGE'], true)) {
            throw ValidationException::withMessages(['type' => 'Jenis movement tidak valid.']);
        }
        $assignmentChanged = $this->changedAny($before, $after, ['departement_id', 'position_id', 'work_location_id', 'supervisor_id']);
        $jobLevelChanged = ($after['job_level_id'] ?? null) !== ($before['job_level_id'] ?? $employee->job_level_id);
        $employmentChanged = $this->changedAny($before, $after, ['employment_status_id', 'employment_type_id']);

        if (in_array($type, ['PROMOTION', 'DEMOTION'], true)) {
            if (! $jobLevelChanged) {
                throw ValidationException::withMessages(['job_level_id' => 'Promotion/demotion wajib mengubah job level.']);
            }
            if ($employmentChanged) {
                throw ValidationException::withMessages(['employment' => 'Promotion/demotion tidak boleh mengubah employment status/type.']);
            }
        }
        if ($type === 'TRANSFER') {
            if ($jobLevelChanged) {
                throw ValidationException::withMessages(['job_level_id' => 'Transfer tidak boleh mengubah job level. Gunakan promotion/demotion.']);
            }
            if ($employmentChanged) {
                throw ValidationException::withMessages(['employment' => 'Transfer tidak boleh mengubah employment status/type. Gunakan employment change.']);
            }
        }
        if ($type === 'EMPLOYMENT_CHANGE') {
            if (! $employmentChanged) {
                throw ValidationException::withMessages(['employment' => 'Employment change wajib mengubah status atau type employment.']);
            }
            if ($assignmentChanged || $jobLevelChanged) {
                throw ValidationException::withMessages(['employment' => 'Employment change tidak boleh mengubah assignment atau job level.']);
            }
        }
    }

    private function changedAny(array $before, array $after, array $fields): bool
    {
        foreach ($fields as $field) {
            if (($after[$field] ?? null) !== ($before[$field] ?? null)) {
                return true;
            }
        }

        return false;
    }

    private function labelSnapshot(array $snapshot, array $labels): array
    {
        return collect(self::PROFILE_FIELDS)->mapWithKeys(function (string $field) use ($snapshot, $labels): array {
            $value = $snapshot[$field] ?? null;

            return [$field => ['id' => $value, 'label' => $value ? ($labels[$field][$value] ?? "#{$value}") : '—']];
        })->all();
    }
}
