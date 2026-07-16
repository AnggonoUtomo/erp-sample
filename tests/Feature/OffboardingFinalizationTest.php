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

class OffboardingFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('offboardings.finalize');
    }

    public function test_ready_case_finalizes_employee_contract_and_offboarding_atomically_on_exit_date(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 20)->setTime(9, 30));
        $actor = $this->actor();
        [$offboarding, $employee, $contract, $activeStatus, $finalStatus] = $this->offboarding();

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();

        $employee->refresh();
        $contract->refresh();
        $offboarding->refresh();
        $this->assertFalse($employee->active);
        $this->assertSame($finalStatus->id, $employee->employment_status_id);
        $this->assertSame('2026-07-20', $employee->ended_at?->toDateString());
        $this->assertSame('ENDED', $contract->status);
        $this->assertSame('2026-07-20', $contract->end_date?->toDateString());
        $this->assertSame($offboarding->exit_reason, $contract->ended_reason);
        $this->assertSame('COMPLETED', $offboarding->status->value);
        $this->assertNull($offboarding->active_identity_key);
        $this->assertSame($actor->id, $offboarding->finalized_by_user_id);
        $this->assertSame('2026-07-20 09:30:00', $offboarding->finalized_at?->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-20', $offboarding->finalization_business_date?->toDateString());
        $this->assertNotSame($activeStatus->id, $employee->employment_status_id);
        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_finalization_before_exit_date_is_rejected_without_partial_mutation(): void
    {
        $actor = $this->actor();
        [$offboarding, $employee, $contract] = $this->offboarding('EMP-OFF-FINAL-EARLY');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-19'])
            ->assertSessionHasErrors('business_date');

        $this->assertTrue($employee->fresh()->active);
        $this->assertSame('ACTIVE', $contract->fresh()->status);
        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_ready_case_without_contract_finalizes_employee_and_offboarding(): void
    {
        $actor = $this->actor();
        [$offboarding, $employee, $contract, , $finalStatus] = $this->offboarding('EMP-OFF-FINAL-NO-CONTRACT', false);

        $this->assertNull($contract);
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->active);
        $this->assertSame($finalStatus->id, $employee->fresh()->employment_status_id);
        $this->assertSame('COMPLETED', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_stale_contract_rolls_back_employee_and_offboarding_mutation(): void
    {
        $actor = $this->actor();
        [$offboarding, $employee, $contract] = $this->offboarding('EMP-OFF-FINAL-STALE');
        $contract->update(['status' => 'ENDED']);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertSessionHasErrors('status');

        $this->assertTrue($employee->fresh()->active);
        $this->assertNull($employee->fresh()->ended_at);
        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_successful_retry_is_idempotent_without_duplicate_audit(): void
    {
        $actor = $this->actor();
        [$offboarding] = $this->offboarding('EMP-OFF-FINAL-RETRY');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();

        $this->assertSame('COMPLETED', $offboarding->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_offboarding_audit_failure_rolls_back_all_owner_mutations(): void
    {
        $actor = $this->actor();
        [$offboarding, $employee, $contract] = $this->offboarding('EMP-OFF-FINAL-AUDIT');
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->times(3)->andReturnUsing(function (...$arguments): void {
                $event = $arguments['event'] ?? $arguments[1] ?? null;
                if ($event === 'Offboarding.finalized') {
                    throw new \RuntimeException('Simulated final audit failure');
                }
            });
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertServerError();

        $this->assertTrue($employee->fresh()->active);
        $this->assertSame('ACTIVE', $contract->fresh()->status);
        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.finalize');

        return $actor;
    }

    /** @return array{Offboarding, Employee, EmployeeContract|null, EmploymentStatus, EmploymentStatus} */
    private function offboarding(string $employeeNumber = 'EMP-OFF-FINAL', bool $withContract = true): array
    {
        $activeStatus = EmploymentStatus::query()->create([
            'code' => 'ACTIVE-'.uniqid(),
            'name' => 'Active',
            'is_final_status' => false,
            'active' => true,
        ]);
        $finalStatus = EmploymentStatus::query()->create([
            'code' => 'ENDED-'.uniqid(),
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $employee = Employee::query()->create([
            'employment_status_id' => $activeStatus->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Final',
            'display_name' => "Final {$employeeNumber}",
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create([
            'code' => 'PERM-'.uniqid(),
            'name' => 'Permanent',
        ]);
        $contract = $withContract
            ? EmployeeContract::query()->create([
                'employee_id' => $employee->id,
                'employment_type_id' => $employmentType->id,
                'contract_number' => "FINAL-{$employee->id}",
                'start_date' => '2026-01-01',
                'status' => 'ACTIVE',
            ])
            : null;
        $template = OffboardingTemplate::query()->create([
            'code' => 'FINAL-'.uniqid(),
            'name' => 'Finalization',
            'active' => true,
        ]);
        $offboarding = Offboarding::query()->create([
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract?->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $finalStatus->id,
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Approved effective exit.',
            'status' => 'READY_FOR_EXIT',
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "final-{$employee->id}"),
        ]);
        $offboarding->tasks()->create([
            'title' => 'Required complete',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'COMPLETED',
        ]);

        return [$offboarding, $employee, $contract, $activeStatus, $finalStatus];
    }
}
