<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingDuplicateGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-07-16 09:00:00');
        Permission::findOrCreate('offboardings.create');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_identical_retry_returns_existing_draft_without_duplicate_tasks_or_audit_side_effect(): void
    {
        [$actor, $employee, $owner, $template, $targetStatus] = $this->context();
        $payload = $this->payload($employee, $owner, $template, $targetStatus);

        $this->actingAs($actor)->post(route('hr.offboardings.store'), $payload)->assertRedirect();
        $this->actingAs($actor)->post(route('hr.offboardings.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('hr_offboardings', 1);
        $this->assertDatabaseCount('hr_offboarding_tasks', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Offboarding.created']);
        $this->assertArrayNotHasKey('active_identity_key', Offboarding::query()->firstOrFail()->toArray());
        $this->assertArrayNotHasKey('request_fingerprint', Offboarding::query()->firstOrFail()->toArray());
    }

    public function test_different_request_for_same_active_employee_identity_is_rejected(): void
    {
        [$actor, $employee, $owner, $template, $targetStatus] = $this->context();
        $payload = $this->payload($employee, $owner, $template, $targetStatus);
        $this->actingAs($actor)->post(route('hr.offboardings.store'), $payload)->assertRedirect();

        $otherOwner = User::factory()->create();
        $this->actingAs($actor)
            ->post(route('hr.offboardings.store'), [
                ...$payload,
                'owner_user_id' => $otherOwner->id,
                'exit_date' => '2026-07-21',
            ])
            ->assertSessionHasErrors('employee_id');

        $this->assertDatabaseCount('hr_offboardings', 1);
        $this->assertDatabaseCount('hr_offboarding_tasks', 1);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_database_unique_guard_prevents_competing_active_rows_for_same_employee(): void
    {
        [, $employee, $owner, $template, $targetStatus] = $this->context();
        $attributes = [
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Uji concurrency guard.',
            'status' => 'DRAFT',
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => str_repeat('a', 64),
        ];
        Offboarding::query()->create($attributes);

        try {
            Offboarding::query()->create([
                ...$attributes,
                'exit_date' => '2026-07-21',
                'request_fingerprint' => str_repeat('b', 64),
            ]);
            $this->fail('Unique active identity constraint did not reject the competing insert.');
        } catch (QueryException) {
            $this->assertDatabaseCount('hr_offboardings', 1);
        }
    }

    /** @return array{User, Employee, User, OffboardingTemplate, EmploymentStatus} */
    private function context(): array
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.create');
        $owner = User::factory()->create();
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-OFF-IDEMP',
            'first_name' => 'Uji',
            'display_name' => 'Uji Offboarding Idempotent',
            'active' => true,
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => 'OFF-IDEMP',
            'name' => 'Offboarding Idempotent',
            'active' => true,
        ]);
        $template->items()->create([
            'title' => 'Task',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'sort_order' => 0,
        ]);
        $targetStatus = EmploymentStatus::query()->create([
            'code' => 'ENDED-IDEMP',
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);

        return [$actor, $employee, $owner, $template, $targetStatus];
    }

    /** @return array<string, mixed> */
    private function payload(
        Employee $employee,
        User $owner,
        OffboardingTemplate $template,
        EmploymentStatus $targetStatus,
    ): array {
        return [
            'employee_id' => $employee->id,
            'employee_contract_id' => null,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Retry yang sama.',
            'notes' => 'Payload harus menghasilkan fingerprint stabil.',
        ];
    }
}
