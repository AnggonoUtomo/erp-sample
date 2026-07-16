<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\DTO\OffboardingDraftData;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class OffboardingService
{
    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly AuditLogService $audit,
    ) {}

    /** @return array<string, mixed> */
    public function getPageData(): array
    {
        $offboardings = Offboarding::query()
            ->with([
                'employee:id,employee_number,display_name',
                'template:id,code,name',
                'targetEmploymentStatus:id,code,name',
                'owner:id,name',
            ])
            ->withCount('tasks')
            ->latest('id')
            ->paginate(20)
            ->through(fn (Offboarding $offboarding) => [
                'id' => $offboarding->id,
                'employee' => $offboarding->employee?->only(['id', 'employee_number', 'display_name']),
                'template' => $offboarding->template?->only(['id', 'code', 'name']),
                'target_status' => $offboarding->targetEmploymentStatus?->only(['id', 'code', 'name']),
                'owner' => $offboarding->owner?->only(['id', 'name']),
                'exit_date' => $offboarding->exit_date?->format('Y-m-d'),
                'exit_type' => $offboarding->exit_type->value,
                'status' => $offboarding->status->value,
                'tasks_count' => $offboarding->tasks_count,
            ]);

        return [
            'businessDate' => now()->toDateString(),
            'offboardings' => $offboardings,
            'employeeOptions' => Employee::query()->where('active', true)->orderBy('display_name')->get(['id', 'employee_number', 'display_name']),
            'contractOptions' => EmployeeContract::query()->where('status', 'ACTIVE')->orderByDesc('start_date')->get(['id', 'employee_id', 'contract_number', 'start_date']),
            'templateOptions' => OffboardingTemplate::availableForOffboarding()->orderBy('name')->get(['id', 'code', 'name']),
            'targetStatusOptions' => EmploymentStatus::query()->where('active', true)->where('is_final_status', true)->orderBy('name')->get(['id', 'code', 'name']),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function createDraft(OffboardingDraftData $data): Offboarding
    {
        $identity = $this->activeIdentity($data);
        $fingerprint = $this->requestFingerprint($data);
        $existing = Offboarding::query()->where('active_identity_key', $identity)->first();

        if ($existing) {
            return $this->resolveExisting($existing, $fingerprint);
        }

        try {
            return $this->transaction->run(function () use ($data, $identity, $fingerprint) {
                $existing = Offboarding::query()
                    ->where('active_identity_key', $identity)
                    ->lockForUpdate()
                    ->first();
                if ($existing) {
                    return $this->resolveExisting($existing, $fingerprint);
                }

                return $this->persistDraft($data, $identity, $fingerprint);
            });
        } catch (QueryException $exception) {
            $existing = Offboarding::query()->where('active_identity_key', $identity)->first();
            if (! $existing) {
                throw $exception;
            }

            return $this->resolveExisting($existing, $fingerprint);
        }
    }

    private function persistDraft(
        OffboardingDraftData $data,
        string $identity,
        string $fingerprint,
    ): Offboarding {
        $this->assertReferencesAreEligible($data);
        $template = OffboardingTemplate::availableForOffboarding()
            ->with('items')
            ->lockForUpdate()
            ->findOrFail($data->templateId);
        $exitDate = CarbonImmutable::parse($data->exitDate);
        $offboarding = Offboarding::query()->create([
            'employee_id' => $data->employeeId,
            'employee_contract_id' => $data->employeeContractId,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $data->targetEmploymentStatusId,
            'owner_user_id' => $data->ownerUserId,
            'exit_date' => $data->exitDate,
            'exit_type' => $data->exitType,
            'exit_reason' => $data->exitReason,
            'notes' => $data->notes,
            'status' => OffboardingStatus::Draft,
            'active_identity_key' => $identity,
            'request_fingerprint' => $fingerprint,
        ]);

        foreach ($template->items as $item) {
            $offboarding->tasks()->create([
                'source_template_item_id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'category' => $item->category,
                'required' => $item->required,
                'due_offset_days' => $item->due_offset_days,
                'due_date' => $exitDate->addDays($item->due_offset_days)->format('Y-m-d'),
                'default_assignee_role' => $item->default_assignee_role,
                'sort_order' => $item->sort_order,
                'status' => OffboardingTaskStatus::Pending,
            ]);
        }

        $this->audit->record(
            module: 'hr.offboardings',
            event: 'Offboarding.created',
            auditable: $offboarding,
            description: "Created draft offboarding {$offboarding->id}",
            newValues: [
                'employee_id' => $data->employeeId,
                'template_id' => $template->id,
                'target_employment_status_id' => $data->targetEmploymentStatusId,
                'exit_date' => $data->exitDate,
                'exit_type' => $data->exitType->value,
                'status' => OffboardingStatus::Draft->value,
                'task_count' => $template->items->count(),
            ],
        );

        return $offboarding->load('tasks');
    }

    private function resolveExisting(Offboarding $existing, string $fingerprint): Offboarding
    {
        if (is_string($existing->request_fingerprint)
            && hash_equals($existing->request_fingerprint, $fingerprint)) {
            return $existing->load('tasks');
        }

        throw ValidationException::withMessages([
            'employee_id' => 'Employee ini sudah memiliki offboarding aktif dengan request berbeda.',
        ]);
    }

    private function activeIdentity(OffboardingDraftData $data): string
    {
        return "employee:{$data->employeeId}";
    }

    private function requestFingerprint(OffboardingDraftData $data): string
    {
        return hash('sha256', json_encode([
            'employee_id' => $data->employeeId,
            'employee_contract_id' => $data->employeeContractId,
            'offboarding_template_id' => $data->templateId,
            'target_employment_status_id' => $data->targetEmploymentStatusId,
            'owner_user_id' => $data->ownerUserId,
            'exit_date' => $data->exitDate,
            'exit_type' => $data->exitType->value,
            'exit_reason' => $data->exitReason,
            'notes' => $data->notes,
        ], JSON_THROW_ON_ERROR));
    }

    private function assertReferencesAreEligible(OffboardingDraftData $data): void
    {
        $employee = Employee::query()
            ->where('active', true)
            ->lockForUpdate()
            ->whereKey($data->employeeId)
            ->first(['id']);
        if (! $employee) {
            throw ValidationException::withMessages(['employee_id' => 'Employee aktif tidak tersedia.']);
        }

        if ($data->employeeContractId !== null) {
            $contract = EmployeeContract::query()
                ->where('employee_id', $data->employeeId)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->whereKey($data->employeeContractId)
                ->first(['id']);
            if (! $contract) {
                throw ValidationException::withMessages(['employee_contract_id' => 'Contract aktif tidak sesuai dengan employee.']);
            }
        }

        $targetStatus = EmploymentStatus::query()
            ->where('active', true)
            ->where('is_final_status', true)
            ->lockForUpdate()
            ->whereKey($data->targetEmploymentStatusId)
            ->first(['id']);
        if (! $targetStatus) {
            throw ValidationException::withMessages(['target_employment_status_id' => 'Status akhir tidak tersedia.']);
        }

        $owner = User::query()
            ->lockForUpdate()
            ->whereKey($data->ownerUserId)
            ->first(['id']);
        if (! $owner) {
            throw ValidationException::withMessages(['owner_user_id' => 'Owner tidak tersedia.']);
        }
    }
}
