<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\DTO\OnboardingDraftData;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Transactions\OnboardingTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class OnboardingService
{
    public function __construct(private readonly OnboardingTransaction $transaction, private readonly AuditLogService $audit) {}

    /** @return array<string, mixed> */
    public function getPageData(array $filters = []): array
    {
        $businessDate = $filters['business_date'] ?? now()->toDateString();
        $query = Onboarding::query()
            ->when($filters['archived'] ?? false, fn ($query) => $query->onlyTrashed())
            ->when($filters['employee_id'] ?? null, fn ($query, $value) => $query->where('employee_id', $value))
            ->when($filters['owner_user_id'] ?? null, fn ($query, $value) => $query->where('owner_user_id', $value))
            ->when($filters['template_id'] ?? null, fn ($query, $value) => $query->where('onboarding_template_id', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['start_from'] ?? null, fn ($query, $value) => $query->whereDate('start_date', '>=', $value))
            ->when($filters['start_to'] ?? null, fn ($query, $value) => $query->whereDate('start_date', '<=', $value))
            ->when($filters['overdue'] ?? false, fn ($query) => $query
                ->whereIn('status', [OnboardingStatus::Draft->value, OnboardingStatus::InProgress->value])
                ->whereHas('tasks', fn ($tasks) => $tasks
                    ->whereIn('status', [OnboardingTaskStatus::Pending->value, OnboardingTaskStatus::InProgress->value])
                    ->whereDate('due_date', '<', $businessDate)));

        $onboardings = $query
            ->with(['employee:id,employee_number,display_name', 'template:id,code,name', 'owner:id,name'])
            ->withCount('tasks')
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Onboarding $onboarding) => [
                'id' => $onboarding->id,
                'employee' => $onboarding->employee?->only(['id', 'employee_number', 'display_name']),
                'template' => $onboarding->template?->only(['id', 'code', 'name']),
                'owner' => $onboarding->owner?->only(['id', 'name']),
                'start_date' => $onboarding->start_date?->format('Y-m-d'),
                'status' => $onboarding->status->value,
                'archived' => $onboarding->trashed(),
                'tasks_count' => $onboarding->tasks_count,
            ]);

        return [
            'businessDate' => $businessDate,
            'filters' => $filters,
            'onboardings' => $onboardings,
            'employeeOptions' => Employee::query()->where('active', true)->orderBy('display_name')->get(['id', 'employee_number', 'display_name']),
            'contractOptions' => EmployeeContract::query()->where('status', '!=', 'CANCELLED')->orderByDesc('start_date')->get(['id', 'employee_id', 'contract_number', 'start_date']),
            'templateOptions' => OnboardingTemplate::availableForOnboarding()->orderBy('name')->get(['id', 'code', 'name']),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function archive(Onboarding $onboarding): void
    {
        $this->transaction->run(function () use ($onboarding) {
            $locked = Onboarding::query()->lockForUpdate()->findOrFail($onboarding->id);
            if (! $locked->status->isTerminal()) {
                throw ValidationException::withMessages(['status' => 'Hanya onboarding terminal yang dapat diarsipkan.']);
            }

            $locked->delete();
            $this->audit->record(module: 'hr.onboardings', event: 'Onboarding.archived', auditable: $locked, description: "Archived onboarding {$locked->id}", oldValues: ['deleted_at' => null], newValues: ['deleted_at' => $locked->deleted_at?->toIso8601String()]);
        });
    }

    public function restore(Onboarding $onboarding): void
    {
        $this->transaction->run(function () use ($onboarding) {
            $locked = Onboarding::query()->withTrashed()->lockForUpdate()->findOrFail($onboarding->id);
            if (! $locked->trashed() || ! $locked->status->isTerminal() || $locked->active_identity_key !== null) {
                throw ValidationException::withMessages(['status' => 'Hanya histori onboarding terminal yang valid dapat direstore.']);
            }

            $archivedAt = $locked->deleted_at?->toIso8601String();
            $locked->restore();
            $this->audit->record(module: 'hr.onboardings', event: 'Onboarding.restored', auditable: $locked, description: "Restored onboarding {$locked->id}", oldValues: ['deleted_at' => $archivedAt], newValues: ['deleted_at' => null]);
        });
    }

    public function createDraft(OnboardingDraftData $data): Onboarding
    {
        $identity = $this->activeIdentity($data);
        $fingerprint = $this->requestFingerprint($data);
        $existing = Onboarding::query()->where('active_identity_key', $identity)->first();

        if ($existing) {
            return $this->resolveExisting($existing, $fingerprint);
        }

        try {
            return $this->transaction->run(function () use ($data, $identity, $fingerprint) {
                $existing = Onboarding::query()->where('active_identity_key', $identity)->lockForUpdate()->first();
                if ($existing) {
                    return $this->resolveExisting($existing, $fingerprint);
                }

                return $this->persistDraft($data, $identity, $fingerprint);
            });
        } catch (QueryException $exception) {
            $existing = Onboarding::query()->where('active_identity_key', $identity)->first();
            if (! $existing) {
                throw $exception;
            }

            return $this->resolveExisting($existing, $fingerprint);
        }
    }

    private function persistDraft(OnboardingDraftData $data, string $identity, string $fingerprint): Onboarding
    {
        $template = OnboardingTemplate::availableForOnboarding()->with('items')->findOrFail($data->templateId);
        $startDate = CarbonImmutable::parse($data->startDate);
        $onboarding = Onboarding::query()->create([
            'employee_id' => $data->employeeId,
            'employee_contract_id' => $data->employeeContractId,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $data->ownerUserId,
            'start_date' => $data->startDate,
            'status' => OnboardingStatus::Draft,
            'active_identity_key' => $identity,
            'request_fingerprint' => $fingerprint,
        ]);

        foreach ($template->items as $item) {
            $onboarding->tasks()->create([
                'source_template_item_id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'category' => $item->category,
                'required' => $item->required,
                'due_offset_days' => $item->due_offset_days,
                'due_date' => $startDate->addDays($item->due_offset_days)->format('Y-m-d'),
                'default_assignee_role' => $item->default_assignee_role,
                'sort_order' => $item->sort_order,
                'status' => OnboardingTaskStatus::Pending,
            ]);
        }

        $this->audit->record(module: 'hr.onboardings', event: 'Onboarding.created', auditable: $onboarding, description: "Created draft onboarding {$onboarding->id}", newValues: ['employee_id' => $data->employeeId, 'template_id' => $template->id, 'start_date' => $data->startDate, 'status' => OnboardingStatus::Draft->value, 'task_count' => $template->items->count()]);

        return $onboarding->load('tasks');
    }

    private function resolveExisting(Onboarding $existing, string $fingerprint): Onboarding
    {
        if (is_string($existing->request_fingerprint) && hash_equals($existing->request_fingerprint, $fingerprint)) {
            return $existing->load('tasks');
        }

        throw ValidationException::withMessages(['employee_id' => 'Employment period ini sudah memiliki onboarding aktif dengan request berbeda.']);
    }

    private function activeIdentity(OnboardingDraftData $data): string
    {
        return $data->employeeContractId !== null
            ? "contract:{$data->employeeContractId}"
            : "employee:{$data->employeeId}:start:{$data->startDate}";
    }

    private function requestFingerprint(OnboardingDraftData $data): string
    {
        return hash('sha256', json_encode([
            'employee_id' => $data->employeeId,
            'employee_contract_id' => $data->employeeContractId,
            'onboarding_template_id' => $data->templateId,
            'owner_user_id' => $data->ownerUserId,
            'start_date' => $data->startDate,
        ], JSON_THROW_ON_ERROR));
    }
}
