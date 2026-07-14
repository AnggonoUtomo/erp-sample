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
    public function getPageData(): array
    {
        $onboardings = Onboarding::query()
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
                'tasks_count' => $onboarding->tasks_count,
            ]);

        return [
            'businessDate' => now()->toDateString(),
            'onboardings' => $onboardings,
            'employeeOptions' => Employee::query()->where('active', true)->orderBy('display_name')->get(['id', 'employee_number', 'display_name']),
            'contractOptions' => EmployeeContract::query()->where('status', '!=', 'CANCELLED')->orderByDesc('start_date')->get(['id', 'employee_id', 'contract_number', 'start_date']),
            'templateOptions' => OnboardingTemplate::availableForOnboarding()->orderBy('name')->get(['id', 'code', 'name']),
            'ownerOptions' => User::query()->orderBy('name')->get(['id', 'name']),
        ];
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
