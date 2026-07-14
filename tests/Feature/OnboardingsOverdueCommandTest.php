<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingsOverdueCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_is_read_only_and_deterministic_for_explicit_date(): void
    {
        $overdue = $this->onboarding('EMP-OVERDUE', 'IN_PROGRESS');
        $overdue->tasks()->create(['title' => 'Late', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'due_date' => '2026-07-09', 'sort_order' => 1, 'status' => 'PENDING']);
        $future = $this->onboarding('EMP-FUTURE', 'IN_PROGRESS');
        $future->tasks()->create(['title' => 'Future', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'due_date' => '2026-07-11', 'sort_order' => 1, 'status' => 'PENDING']);
        $before = [Onboarding::count(), $overdue->tasks()->count() + $future->tasks()->count()];

        $this->artisan('hr:onboardings:overdue', ['--date' => '2026-07-10'])
            ->expectsOutputToContain('EMP-OVERDUE')
            ->doesntExpectOutputToContain('EMP-FUTURE')
            ->assertSuccessful();

        $this->assertSame($before, [Onboarding::count(), $overdue->tasks()->count() + $future->tasks()->count()]);
    }

    public function test_command_rejects_invalid_or_missing_date_with_clear_exit_code(): void
    {
        $this->artisan('hr:onboardings:overdue')->assertFailed();
        $this->artisan('hr:onboardings:overdue', ['--date' => '10-07-2026'])->assertFailed();
    }

    private function onboarding(string $employeeNumber, string $status): Onboarding
    {
        $employee = Employee::query()->create(['employee_number' => $employeeNumber, 'first_name' => 'Command', 'display_name' => $employeeNumber, 'active' => true]);
        $template = OnboardingTemplate::query()->create(['code' => 'CMD-'.uniqid(), 'name' => 'Command', 'active' => true]);

        return Onboarding::query()->create([
            'employee_id' => $employee->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => User::factory()->create()->id,
            'start_date' => '2026-07-01',
            'status' => $status,
            'active_identity_key' => "employee:{$employee->id}:start:2026-07-01",
            'request_fingerprint' => hash('sha256', "command-{$employee->id}"),
        ]);
    }
}
