<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['offboardings.view', 'offboardings.activate'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_activates_eligible_draft_atomically_without_employment_or_snapshot_changes(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.activate');
        $offboarding = $this->offboarding('DRAFT', withContract: true);
        $employeeBefore = $offboarding->employee->fresh()->getAttributes();
        $contractBefore = $offboarding->contract?->fresh()->getAttributes();
        $taskBefore = $offboarding->tasks()->firstOrFail()->fresh()->getAttributes();
        $exitContextFields = [
            'exit_date',
            'exit_type',
            'exit_reason',
            'notes',
            'target_employment_status_id',
        ];
        $exitContextBefore = $offboarding->only($exitContextFields);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.activate', $offboarding))
            ->assertRedirect(route('hr.offboardings.show', [
                'offboarding' => $offboarding,
                'business_date' => now()->toDateString(),
            ]));

        $this->assertDatabaseHas('hr_offboardings', [
            'id' => $offboarding->id,
            'status' => 'IN_PROGRESS',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'module' => 'hr.offboardings',
            'event' => 'Offboarding.activated',
            'auditable_id' => $offboarding->id,
        ]);
        $this->assertSame($employeeBefore, $offboarding->employee->fresh()->getAttributes());
        $this->assertSame($contractBefore, $offboarding->contract?->fresh()->getAttributes());
        $this->assertSame($taskBefore, $offboarding->tasks()->firstOrFail()->fresh()->getAttributes());
        $this->assertEquals($exitContextBefore, $offboarding->fresh()->only($exitContextFields));
    }

    public function test_repeated_activation_is_idempotent_without_second_audit(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.activate');
        $offboarding = $this->offboarding('DRAFT');

        $this->actingAs($actor)->patch(route('hr.offboardings.activate', $offboarding))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.offboardings.activate', $offboarding))->assertRedirect();

        $this->assertSame('IN_PROGRESS', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_archived_invalid_state_and_missing_or_mismatched_identity_are_rejected(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.activate');
        $terminal = $this->offboarding('COMPLETED', employeeNumber: 'EMP-OFF-TERMINAL');
        $missingIdentity = $this->offboarding('DRAFT', false, 'EMP-OFF-NO-IDENTITY');
        $mismatchedIdentity = $this->offboarding('DRAFT', true, 'EMP-OFF-WRONG-IDENTITY');
        $mismatchedIdentity->update(['active_identity_key' => 'employee:999999']);
        $archived = $this->offboarding('DRAFT', true, 'EMP-OFF-ARCHIVED');
        $archived->delete();

        foreach ([$terminal, $missingIdentity, $mismatchedIdentity, $archived] as $offboarding) {
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.activate', $offboarding))
                ->assertSessionHasErrors('status');
        }

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_stale_employee_contract_target_status_owner_and_duplicate_case_are_rejected(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.activate');
        $inactiveEmployee = $this->offboarding('DRAFT', employeeNumber: 'EMP-OFF-INACTIVE');
        $inactiveEmployee->employee->update(['active' => false]);
        $staleContract = $this->offboarding('DRAFT', true, 'EMP-OFF-CONTRACT', true);
        $staleContract->contract?->update(['status' => 'ENDED']);
        $staleTarget = $this->offboarding('DRAFT', false, 'EMP-OFF-TARGET');
        $staleTarget->targetEmploymentStatus->update(['active' => false]);
        $deletedOwner = $this->offboarding('DRAFT', false, 'EMP-OFF-OWNER');
        $deletedOwner->owner->delete();
        $duplicate = $this->offboarding('DRAFT', false, 'EMP-OFF-DUPLICATE');
        Offboarding::query()->create([
            ...$duplicate->only([
                'employee_id',
                'offboarding_template_id',
                'target_employment_status_id',
                'owner_user_id',
                'exit_date',
                'exit_type',
                'exit_reason',
                'notes',
            ]),
            'status' => 'IN_PROGRESS',
            'active_identity_key' => "legacy:{$duplicate->employee_id}",
            'request_fingerprint' => hash('sha256', 'legacy-duplicate'),
        ]);

        foreach ([$inactiveEmployee, $staleContract, $staleTarget, $deletedOwner, $duplicate] as $offboarding) {
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.activate', $offboarding))
                ->assertSessionHasErrors('status');
        }

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_audit_failure_rolls_back_activation(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.activate');
        $offboarding = $this->offboarding('DRAFT');
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.activate', $offboarding))
            ->assertServerError();

        $this->assertSame('DRAFT', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_activation_is_permission_protected(): void
    {
        $unauthorized = User::factory()->create();
        $offboarding = $this->offboarding('DRAFT');

        $this->actingAs($unauthorized)
            ->patch(route('hr.offboardings.activate', $offboarding))
            ->assertForbidden();

        $this->assertSame('DRAFT', $offboarding->fresh()->status->value);
    }

    private function offboarding(
        string $status,
        bool $withIdentity = true,
        string $employeeNumber = 'EMP-OFF-ACTIVATE',
        bool $withContract = false,
    ): Offboarding {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $owner = User::factory()->create();
        $template = OffboardingTemplate::query()->create([
            'code' => "ACTIVATE-{$employee->id}",
            'name' => 'Activation Template',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => "ENDED-{$employee->id}",
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $contract = $withContract ? $this->contract($employee) : null;
        $offboarding = Offboarding::query()->create([
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract?->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Activation test.',
            'notes' => 'Snapshot tetap.',
            'status' => $status,
            'active_identity_key' => $withIdentity ? "employee:{$employee->id}" : null,
            'request_fingerprint' => $withIdentity ? hash('sha256', "activation-{$employee->id}") : null,
        ]);
        $offboarding->tasks()->create([
            'title' => 'Snapshot task',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'PENDING',
        ]);

        return $offboarding->load(['employee', 'contract', 'targetEmploymentStatus', 'owner', 'tasks']);
    }

    private function contract(Employee $employee): EmployeeContract
    {
        $employmentType = EmploymentType::query()->firstOrCreate(
            ['code' => 'PERM-ACTIVATION'],
            ['name' => 'Permanent'],
        );

        return EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => "CTR-{$employee->employee_number}",
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
    }
}
