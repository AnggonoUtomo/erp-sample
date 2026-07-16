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

class OffboardingCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('offboardings.cancel');
    }

    public function test_draft_in_progress_and_ready_cases_can_be_cancelled_with_evidence(): void
    {
        $this->travelTo(now()->setDate(2026, 7, 16)->setTime(14, 20));
        $actor = $this->actor();

        foreach (['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'] as $index => $status) {
            $offboarding = $this->offboarding($status, "EMP-OFF-CANCEL-{$index}");

            $this->actingAs($actor)
                ->patch(route('hr.offboardings.cancel', $offboarding), [])
                ->assertSessionHasErrors('reason');
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.cancel', $offboarding), [
                    'reason' => "Dibatalkan dari {$status}.",
                ])
                ->assertRedirect();

            $fresh = $offboarding->fresh();
            $this->assertSame('CANCELLED', $fresh->status->value);
            $this->assertSame($actor->id, $fresh->cancelled_by_user_id);
            $this->assertSame('2026-07-16 14:20:00', $fresh->cancelled_at?->format('Y-m-d H:i:s'));
            $this->assertSame("Dibatalkan dari {$status}.", $fresh->cancel_reason);
            $this->assertNull($fresh->active_identity_key);
        }

        $this->assertDatabaseCount('audit_logs', 3);
    }

    public function test_cancel_preserves_employee_contract_exit_context_and_task_snapshot(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding('READY_FOR_EXIT', 'EMP-OFF-CANCEL-INVARIANT', true);
        $employeeBefore = $offboarding->employee->fresh()->getAttributes();
        $contractBefore = $offboarding->contract?->fresh()->getAttributes();
        $taskBefore = $offboarding->tasks()->firstOrFail()->fresh()->getAttributes();
        $exitBefore = $offboarding->only([
            'exit_date',
            'exit_type',
            'exit_reason',
            'notes',
            'target_employment_status_id',
        ]);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.cancel', $offboarding), [
                'reason' => 'Rencana exit dibatalkan.',
            ])
            ->assertRedirect();

        $this->assertSame($employeeBefore, $offboarding->employee->fresh()->getAttributes());
        $this->assertSame($contractBefore, $offboarding->contract?->fresh()->getAttributes());
        $this->assertSame($taskBefore, $offboarding->tasks()->firstOrFail()->fresh()->getAttributes());
        $this->assertEquals($exitBefore, $offboarding->fresh()->only(array_keys($exitBefore)));
    }

    public function test_completed_cancelled_archived_and_unauthorized_cases_are_rejected(): void
    {
        $actor = $this->actor();
        $unauthorized = User::factory()->create();
        $completed = $this->offboarding('COMPLETED', 'EMP-OFF-CANCEL-COMPLETED');
        $cancelled = $this->offboarding('CANCELLED', 'EMP-OFF-CANCEL-CANCELLED');
        $archived = $this->offboarding('IN_PROGRESS', 'EMP-OFF-CANCEL-ARCHIVED');
        $archived->delete();
        $active = $this->offboarding('IN_PROGRESS', 'EMP-OFF-CANCEL-DENIED');

        foreach ([$completed, $cancelled, $archived] as $offboarding) {
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.cancel', $offboarding), ['reason' => 'Tidak boleh.'])
                ->assertSessionHasErrors('status');
        }
        $this->actingAs($unauthorized)
            ->patch(route('hr.offboardings.cancel', $active), ['reason' => 'Tidak berizin.'])
            ->assertForbidden();

        $this->assertSame('COMPLETED', $completed->fresh()->status->value);
        $this->assertSame('CANCELLED', $cancelled->fresh()->status->value);
        $this->assertSame('IN_PROGRESS', $archived->fresh()->status->value);
        $this->assertSame('IN_PROGRESS', $active->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_reason_is_trimmed_and_bounded(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding('DRAFT', 'EMP-OFF-CANCEL-REASON');

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.cancel', $offboarding), [
                'reason' => str_repeat('a', 2001),
            ])
            ->assertSessionHasErrors('reason');
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.cancel', $offboarding), [
                'reason' => '  Dibatalkan secara terkontrol.  ',
            ])
            ->assertRedirect();

        $this->assertSame('Dibatalkan secara terkontrol.', $offboarding->fresh()->cancel_reason);
    }

    public function test_audit_failure_rolls_back_cancellation_and_active_identity(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding('IN_PROGRESS', 'EMP-OFF-CANCEL-ROLLBACK');
        $identity = $offboarding->active_identity_key;
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.cancel', $offboarding), [
                'reason' => 'Harus rollback.',
            ])
            ->assertServerError();

        $fresh = $offboarding->fresh();
        $this->assertSame('IN_PROGRESS', $fresh->status->value);
        $this->assertSame($identity, $fresh->active_identity_key);
        $this->assertNull($fresh->cancelled_by_user_id);
        $this->assertNull($fresh->cancelled_at);
        $this->assertNull($fresh->cancel_reason);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.cancel');

        return $actor;
    }

    private function offboarding(
        string $status,
        string $employeeNumber,
        bool $withContract = false,
    ): Offboarding {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => "CANCEL-{$employee->id}",
            'name' => 'Cancellation',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => "ENDED-CANCEL-{$employee->id}",
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
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Cancellation test.',
            'notes' => 'Snapshot tetap.',
            'status' => $status,
            'active_identity_key' => in_array($status, ['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'], true)
                ? "employee:{$employee->id}"
                : null,
            'request_fingerprint' => hash('sha256', "cancel-{$employee->id}"),
        ]);
        $offboarding->tasks()->create([
            'title' => 'Snapshot task',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'COMPLETED',
        ]);

        return $offboarding->load(['employee', 'contract', 'tasks']);
    }

    private function contract(Employee $employee): EmployeeContract
    {
        $employmentType = EmploymentType::query()->firstOrCreate(
            ['code' => 'PERM-CANCEL'],
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
