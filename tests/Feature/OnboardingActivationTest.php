<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingActivationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.activate'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_activates_draft_atomically_and_audits_transition(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.activate');
        $onboarding = $this->onboarding('DRAFT');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.activate', $onboarding))
            ->assertRedirect(route('hr.onboardings.show', [
                'onboarding' => $onboarding,
                'business_date' => now()->toDateString(),
            ]));

        $this->assertDatabaseHas('hr_onboardings', ['id' => $onboarding->id, 'status' => 'IN_PROGRESS']);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'module' => 'hr.onboardings',
            'event' => 'Onboarding.activated',
            'auditable_id' => $onboarding->id,
        ]);
    }

    public function test_repeated_activation_is_idempotent_without_second_audit(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.activate');
        $onboarding = $this->onboarding('DRAFT');

        $this->actingAs($actor)->patch(route('hr.onboardings.activate', $onboarding))->assertRedirect();
        $this->actingAs($actor)->patch(route('hr.onboardings.activate', $onboarding))->assertRedirect();

        $this->assertDatabaseCount('hr_onboardings', 1);
        $this->assertDatabaseCount('audit_logs', 1);
        $this->assertSame('IN_PROGRESS', $onboarding->fresh()->status->value);
    }

    public function test_terminal_state_and_missing_active_identity_are_rejected_without_changes(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.activate');
        $terminal = $this->onboarding('COMPLETED');
        $missingIdentity = $this->onboarding('DRAFT', false, 'EMP-NO-IDENTITY');

        $this->actingAs($actor)
            ->patch(route('hr.onboardings.activate', $terminal))
            ->assertSessionHasErrors('status');
        $this->actingAs($actor)
            ->patch(route('hr.onboardings.activate', $missingIdentity))
            ->assertSessionHasErrors('status');

        $this->assertSame('COMPLETED', $terminal->fresh()->status->value);
        $this->assertSame('DRAFT', $missingIdentity->fresh()->status->value);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_activation_is_permission_protected(): void
    {
        $unauthorized = User::factory()->create();
        $onboarding = $this->onboarding('DRAFT');

        $this->actingAs($unauthorized)
            ->patch(route('hr.onboardings.activate', $onboarding))
            ->assertForbidden();

        $this->assertSame('DRAFT', $onboarding->fresh()->status->value);
    }

    private function onboarding(string $status, bool $withIdentity = true, string $employeeNumber = 'EMP-ACTIVATE'): Onboarding
    {
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Uji',
            'display_name' => "Uji {$employeeNumber}",
            'active' => true,
        ]);
        $owner = User::factory()->create();
        $template = OnboardingTemplate::query()->create([
            'code' => "ACTIVATE-{$employee->id}",
            'name' => 'Activation Template',
            'active' => true,
        ]);

        return Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-20',
            'status' => $status,
            'active_identity_key' => $withIdentity ? "employee:{$employee->id}:start:2026-07-20" : null,
            'request_fingerprint' => $withIdentity ? hash('sha256', "activation-{$employee->id}") : null,
        ]);
    }
}
