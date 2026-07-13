<?php

namespace App\Modules\HR\EmployeeContracts\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\DTO\EmployeeContractData;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Transactions\EmployeeContractsTransaction;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Validation\ValidationException;

class EmployeeContractsService
{
    public function __construct(private EmployeeContractsTransaction $transaction, private AuditLogService $audit) {}

    public function pageData(): array
    {
        return [
            'contracts' => EmployeeContract::query()->with(['employee', 'employmentType'])->latest('start_date')->paginate(15)->through(fn ($contract) => [
                'id' => $contract->id, 'employee_id' => $contract->employee_id, 'employment_type_id' => $contract->employment_type_id,
                'contract_number' => $contract->contract_number, 'start_date' => $contract->start_date->toDateString(),
                'end_date' => $contract->end_date?->toDateString(), 'status' => $contract->status, 'notes' => $contract->notes,
                'employee' => ['id' => $contract->employee->id, 'display_name' => $contract->employee->display_name],
                'employment_type' => ['id' => $contract->employmentType->id, 'name' => $contract->employmentType->name],
            ]),
            'options' => [
                'employees' => Employee::query()->where('active', true)->orderBy('display_name')->get()->map(fn ($item) => ['value' => $item->id, 'label' => "{$item->display_name} ({$item->employee_number})"]),
                'employmentTypes' => EmploymentType::query()->where('active', true)->orderBy('name')->get()->map(fn ($item) => ['value' => $item->id, 'label' => $item->name]),
            ],
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
}
