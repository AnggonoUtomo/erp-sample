<?php

namespace App\Modules\HR\EmployeeMovements\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeMovements\DTO\EmployeeMovementData;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\EmployeeMovements\Transactions\EmployeeMovementsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Validation\ValidationException;

class EmployeeMovementsService
{
    private const PROFILE_FIELDS = ['departement_id', 'position_id', 'work_location_id', 'supervisor_id'];

    public function __construct(
        private EmployeeMovementsTransaction $transaction,
        private AuditLogService $audit,
    ) {}

    public function pageData(): array
    {
        $departments = Departement::query()->where('active', true)->orderBy('name')->get(['id', 'name']);
        $positions = Position::query()->where('active', true)->orderBy('name')->get(['id', 'departement_id', 'name']);
        $locations = WorkLocation::query()->where('active', true)->orderBy('name')->get(['id', 'name']);
        $employees = Employee::query()->where('active', true)->orderBy('display_name')->get(['id', 'employee_number', 'display_name']);
        $labels = [
            'departement_id' => $departments->pluck('name', 'id'),
            'position_id' => $positions->pluck('name', 'id'),
            'work_location_id' => $locations->pluck('name', 'id'),
            'supervisor_id' => $employees->pluck('display_name', 'id'),
        ];

        return [
            'movements' => EmployeeMovement::query()->with(['employee:id,display_name,employee_number', 'creator:id,name', 'applier:id,name'])
                ->latest()->paginate(15)->through(function (EmployeeMovement $movement) use ($labels): array {
                    return [
                        'id' => $movement->id, 'type' => $movement->type, 'effective_date' => $movement->effective_date->toDateString(),
                        'status' => $movement->status, 'reason' => $movement->reason, 'notes' => $movement->notes,
                        'employee' => $movement->employee->only(['id', 'display_name', 'employee_number']),
                        'before' => $this->labelSnapshot($movement->before_values, $labels),
                        'after' => $this->labelSnapshot($movement->after_values, $labels),
                        'creator' => $movement->creator?->only(['id', 'name']),
                        'applier' => $movement->applier?->only(['id', 'name']),
                        'applied_at' => $movement->applied_at?->toISOString(),
                    ];
                }),
            'options' => [
                'today' => now()->toDateString(),
                'employees' => $employees->map(fn (Employee $employee) => ['value' => $employee->id, 'label' => "{$employee->display_name} ({$employee->employee_number})"]),
                'departments' => $departments->map(fn (Departement $item) => ['value' => $item->id, 'label' => $item->name]),
                'positions' => $positions->map(fn (Position $item) => ['value' => $item->id, 'departement_id' => $item->departement_id, 'label' => $item->name]),
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
                'work_location_id' => $data->workLocationId ?? $employee->work_location_id,
                'supervisor_id' => $data->supervisorId ?? $employee->supervisor_id,
            ];
            $this->validateTarget($employee, $after);
            if ($before === $after) {
                throw ValidationException::withMessages(['movement' => 'Movement harus mengubah minimal satu assignment employee.']);
            }

            $movement = EmployeeMovement::query()->create([
                'employee_id' => $employee->id, 'type' => 'TRANSFER', 'effective_date' => $data->effectiveDate,
                'status' => 'DRAFT', 'reason' => $data->reason, 'notes' => $data->notes,
                'before_values' => $before, 'after_values' => $after, 'created_by' => auth()->id(),
            ]);
            $this->audit->record(
                module: 'hr.employee-movements', event: 'EmployeeMovement.created', auditable: $movement,
                description: "Created transfer draft for {$employee->display_name}", newValues: $movement->toArray(),
            );

            return $movement;
        });
    }

    public function apply(EmployeeMovement $movement): EmployeeMovement
    {
        return $this->transaction->run(function () use ($movement): EmployeeMovement {
            $locked = EmployeeMovement::query()->lockForUpdate()->findOrFail($movement->id);
            if ($locked->status !== 'DRAFT') {
                throw ValidationException::withMessages(['status' => 'Hanya movement DRAFT yang dapat diterapkan.']);
            }
            if ($locked->effective_date->toDateString() !== now()->toDateString()) {
                throw ValidationException::withMessages(['effective_date' => 'Movement hanya dapat diterapkan tepat pada effective date.']);
            }

            $employee = Employee::query()->lockForUpdate()->findOrFail($locked->employee_id);
            if ($this->snapshot($employee) !== $this->normalizeSnapshot($locked->before_values)) {
                throw ValidationException::withMessages(['profile' => 'Profile employee telah berubah. Buat movement baru dari data terkini.']);
            }
            $after = $this->normalizeSnapshot($locked->after_values);
            $this->validateTarget($employee, $after);
            $employee->update($after);
            $locked->update(['status' => 'APPLIED', 'applied_by' => auth()->id(), 'applied_at' => now()]);
            $this->audit->record(
                module: 'hr.employee-movements', event: 'EmployeeMovement.applied', auditable: $locked,
                description: "Applied transfer for {$employee->display_name}",
                oldValues: $locked->before_values, newValues: $after,
            );

            return $locked->refresh();
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

    private function validateTarget(Employee $employee, array $target): void
    {
        if ($target['supervisor_id'] === $employee->id) {
            throw ValidationException::withMessages(['supervisor_id' => 'Employee tidak boleh menjadi supervisor dirinya sendiri.']);
        }
        if ($target['position_id'] && Position::query()->whereKey($target['position_id'])->where('departement_id', $target['departement_id'])->doesntExist()) {
            throw ValidationException::withMessages(['position_id' => 'Position harus berada pada department tujuan.']);
        }
    }

    private function labelSnapshot(array $snapshot, array $labels): array
    {
        return collect(self::PROFILE_FIELDS)->mapWithKeys(function (string $field) use ($snapshot, $labels): array {
            $value = $snapshot[$field] ?? null;

            return [$field => ['id' => $value, 'label' => $value ? ($labels[$field][$value] ?? "#{$value}") : '—']];
        })->all();
    }
}
