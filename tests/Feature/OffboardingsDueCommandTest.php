<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffboardingsDueCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_is_read_only_and_deterministic_for_explicit_date_and_window(): void
    {
        $due = $this->offboarding('EMP-OFF-DUE', 'IN_PROGRESS', '2026-07-20');
        $due->tasks()->create(['title' => 'Due soon', 'category' => 'HR', 'required' => true, 'due_offset_days' => -10, 'due_date' => '2026-07-10', 'sort_order' => 1, 'status' => 'PENDING']);
        $future = $this->offboarding('EMP-OFF-FUTURE', 'IN_PROGRESS', '2026-08-31');
        $future->tasks()->create(['title' => 'Future task', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'due_date' => '2026-08-31', 'sort_order' => 1, 'status' => 'PENDING']);
        $completed = $this->offboarding('EMP-OFF-DONE', 'COMPLETED', '2026-07-09');
        $completed->tasks()->create(['title' => 'Historical', 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'due_date' => '2026-07-09', 'sort_order' => 1, 'status' => 'PENDING']);
        $before = [Offboarding::count(), $due->tasks()->count() + $future->tasks()->count() + $completed->tasks()->count()];

        $this->artisan('hr:offboardings:due', ['--date' => '2026-07-10', '--within' => 30])
            ->expectsOutputToContain('EMP-OFF-DUE')
            ->doesntExpectOutputToContain('EMP-OFF-FUTURE')
            ->doesntExpectOutputToContain('EMP-OFF-DONE')
            ->assertSuccessful();

        $this->assertSame($before, [Offboarding::count(), $due->tasks()->count() + $future->tasks()->count() + $completed->tasks()->count()]);
    }

    public function test_command_rejects_invalid_missing_or_unbounded_options_with_clear_exit_code(): void
    {
        $this->artisan('hr:offboardings:due')->assertFailed();
        $this->artisan('hr:offboardings:due', ['--date' => '10-07-2026'])->assertFailed();
        $this->artisan('hr:offboardings:due', ['--date' => '2026-07-10', '--within' => 366])->assertFailed();
        $this->artisan('hr:offboardings:due', ['--date' => '2026-07-10', '--within' => -1])->assertFailed();
    }

    private function offboarding(string $employeeNumber, string $status, string $exitDate): Offboarding
    {
        $finalStatus = EmploymentStatus::query()->create(['code' => 'ENDED-'.uniqid(), 'name' => 'Ended', 'is_final_status' => true, 'active' => true]);
        $employee = Employee::query()->create([
            'employee_number' => $employeeNumber,
            'first_name' => 'Command',
            'display_name' => $employeeNumber,
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
        $template = OffboardingTemplate::query()->create(['code' => 'CMD-'.uniqid(), 'name' => 'Command', 'active' => true]);

        return Offboarding::query()->create([
            'employee_id' => $employee->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $finalStatus->id,
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => $exitDate,
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Command check.',
            'status' => $status,
            'active_identity_key' => in_array($status, ['DRAFT', 'IN_PROGRESS', 'READY_FOR_EXIT'], true) ? "employee:{$employee->id}" : null,
            'request_fingerprint' => hash('sha256', "command-{$employee->id}"),
        ]);
    }
}
