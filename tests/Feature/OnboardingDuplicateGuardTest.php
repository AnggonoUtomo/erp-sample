<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingDuplicateGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('onboardings.create');
    }

    public function test_identical_retry_returns_existing_draft_without_duplicate_tasks_or_audit_side_effect(): void
    {
        [$user, $employee, $owner, $template] = $this->context();
        $payload = $this->payload($employee, $owner, $template);

        $this->actingAs($user)->post(route('hr.onboardings.store'), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('hr.onboardings.store'), $payload)->assertRedirect();

        $this->assertDatabaseCount('hr_onboardings', 1);
        $this->assertDatabaseCount('hr_onboarding_tasks', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertDatabaseHas('audit_logs', ['event' => 'Onboarding.created']);
    }

    public function test_different_request_for_same_active_employment_identity_is_rejected(): void
    {
        [$user, $employee, $owner, $template] = $this->context();
        $payload = $this->payload($employee, $owner, $template);
        $this->actingAs($user)->post(route('hr.onboardings.store'), $payload)->assertRedirect();

        $otherOwner = User::factory()->create();
        $this->actingAs($user)
            ->post(route('hr.onboardings.store'), [...$payload, 'owner_user_id' => $otherOwner->id])
            ->assertSessionHasErrors('employee_id');

        $this->assertDatabaseCount('hr_onboardings', 1);
        $this->assertDatabaseCount('hr_onboarding_tasks', 1);
    }

    public function test_database_unique_guard_prevents_two_active_rows_for_same_identity(): void
    {
        [, $employee, $owner, $template] = $this->context();
        $attributes = [
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-20',
            'status' => 'DRAFT',
            'active_identity_key' => "employee:{$employee->id}:start:2026-07-20",
            'request_fingerprint' => str_repeat('a', 64),
        ];
        Onboarding::query()->create($attributes);

        try {
            Onboarding::query()->create([...$attributes, 'request_fingerprint' => str_repeat('b', 64)]);
            $this->fail('Unique active identity constraint did not reject the competing insert.');
        } catch (QueryException) {
            $this->assertDatabaseCount('hr_onboardings', 1);
        }
    }

    /** @return array{User, Employee, User, OnboardingTemplate} */
    private function context(): array
    {
        $user = User::factory()->create();
        $user->givePermissionTo('onboardings.create');
        $owner = User::factory()->create();
        $employee = Employee::query()->create(['employee_number' => 'EMP-IDEMP', 'first_name' => 'Uji', 'display_name' => 'Uji Idempotent', 'active' => true]);
        $template = OnboardingTemplate::query()->create(['code' => 'IDEMP', 'name' => 'Idempotent', 'active' => true]);
        $template->items()->create(['title' => 'Task', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'sort_order' => 0]);

        return [$user, $employee, $owner, $template];
    }

    /** @return array<string, mixed> */
    private function payload(Employee $employee, User $owner, OnboardingTemplate $template): array
    {
        return ['employee_id' => $employee->id, 'employee_contract_id' => null, 'onboarding_template_id' => $template->id, 'owner_user_id' => $owner->id, 'start_date' => '2026-07-20'];
    }
}
