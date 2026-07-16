<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('offboardings.mark-ready');
    }

    public function test_required_incomplete_blocks_ready_but_optional_incomplete_is_allowed(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding();
        $required = $offboarding->tasks()->create([
            'title' => 'Required',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'PENDING',
        ]);
        $offboarding->tasks()->create([
            'title' => 'Optional',
            'category' => 'HR',
            'required' => false,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 1,
            'status' => 'PENDING',
        ]);

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.mark-ready', $offboarding))
            ->assertSessionHasErrors('status');

        $required->update(['status' => 'COMPLETED']);
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.mark-ready', $offboarding))
            ->assertRedirect();

        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'Offboarding.marked_ready',
            'auditable_id' => $offboarding->id,
        ]);
    }

    public function test_ready_retry_is_idempotent_and_does_not_change_employment_data(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding();
        $employeeBefore = $offboarding->employee->fresh()->getAttributes();
        $offboarding->tasks()->create([
            'title' => 'Required complete',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'COMPLETED',
        ]);

        $this->actingAs($actor)->patch(route('hr.offboardings.mark-ready', $offboarding))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.offboardings.mark-ready', $offboarding))->assertRedirect();

        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
        $this->assertSame($employeeBefore, $offboarding->employee->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_invalid_archived_terminal_and_unauthorized_ready_are_rejected(): void
    {
        $actor = $this->actor();
        $unauthorized = User::factory()->create();
        $draft = $this->offboarding('DRAFT', 'EMP-OFF-READY-DRAFT');
        $archived = $this->offboarding('IN_PROGRESS', 'EMP-OFF-READY-ARCHIVED');
        $archived->delete();
        $terminal = $this->offboarding('CANCELLED', 'EMP-OFF-READY-CANCELLED');
        $active = $this->offboarding('IN_PROGRESS', 'EMP-OFF-READY-DENIED');

        foreach ([$draft, $archived, $terminal] as $offboarding) {
            $this->actingAs($actor)
                ->patch(route('hr.offboardings.mark-ready', $offboarding))
                ->assertSessionHasErrors('status');
        }
        $this->actingAs($unauthorized)
            ->patch(route('hr.offboardings.mark-ready', $active))
            ->assertForbidden();

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_audit_failure_rolls_back_ready_transition(): void
    {
        $actor = $this->actor();
        $offboarding = $this->offboarding();
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.mark-ready', $offboarding))
            ->assertServerError();

        $this->assertSame('IN_PROGRESS', $offboarding->fresh()->status->value);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.mark-ready');

        return $actor;
    }

    private function offboarding(
        string $status = 'IN_PROGRESS',
        string $employeeNumber = 'EMP-OFF-READY',
    ): Offboarding {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => "READY-{$employee->id}",
            'name' => 'Ready',
            'active' => true,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => "ENDED-READY-{$employee->id}",
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);

        return Offboarding::query()->create([
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Readiness test.',
            'status' => $status,
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "ready-{$employee->id}"),
        ])->load('employee');
    }
}
