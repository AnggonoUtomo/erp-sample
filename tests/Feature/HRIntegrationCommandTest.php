<?php

namespace Tests\Feature;

use App\Modules\HR\Employees\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HRIntegrationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_describe_command_lists_snapshot_and_event_contract_versions(): void
    {
        $this->artisan('hr:integration-contracts:describe')
            ->expectsOutputToContain('HR Integration Contracts v1')
            ->expectsTable(
                ['Type', 'Contract', 'Version'],
                [
                    ['Snapshot', 'EmployeeSnapshotV1', 1],
                    ['Snapshot', 'EmployeeAssignmentSnapshotV1', 1],
                    ['Snapshot', 'EmployeeContractSnapshotV1', 1],
                    ['Snapshot', 'EmployeeDocumentComplianceSnapshotV1', 1],
                    ['Event', 'EmployeeCreatedV1', 1],
                    ['Event', 'EmployeeProfileUpdatedV1', 1],
                    ['Event', 'EmployeeAssignmentChangedV1', 1],
                    ['Event', 'EmployeeContractChangedV1', 1],
                    ['Event', 'EmployeeDocumentComplianceChangedV1', 1],
                    ['Event', 'EmployeeOnboardingActivatedV1', 1],
                    ['Event', 'EmployeeOnboardingCompletedV1', 1],
                    ['Event', 'EmployeeOffboardingReadyV1', 1],
                    ['Event', 'EmployeeOffboardingFinalizedV1', 1],
                    ['Event', 'EmploymentTerminatedV1', 1],
                ],
            )
            ->assertSuccessful();
    }

    public function test_validate_command_checks_registry_manifest_and_privacy_guard_without_mutating_state(): void
    {
        Notification::fake();
        Queue::fake();

        $this->artisan('hr:integration-contracts:validate')
            ->expectsOutputToContain('HR Integration Contracts validation passed.')
            ->expectsOutputToContain('Snapshot contracts: 4')
            ->expectsOutputToContain('Event contracts: 10')
            ->expectsOutputToContain('Downstream listeners: deferred')
            ->assertSuccessful();

        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_sample_command_outputs_safe_employee_payload_without_sensitive_fields(): void
    {
        Notification::fake();
        Queue::fake();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-INT-CMD-001',
            'first_name' => 'Intan',
            'display_name' => 'Intan Integration',
            'work_email' => 'intan.integration@mail.test',
            'personal_email' => 'private@mail.test',
            'phone' => '08123456789',
            'national_id' => 'SECRET-NIK',
            'address' => 'Secret address',
            'notes' => 'confidential command note',
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
        $before = $employee->fresh()->getAttributes();

        $exitCode = Artisan::call('hr:integration-contracts:sample', [
            'employeeId' => $employee->id,
            '--date' => '2026-07-18',
            '--warning-days' => '7',
        ]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('employee', $output);
        $this->assertStringContainsString('assignment', $output);
        $this->assertStringContainsString('contract', $output);
        $this->assertStringContainsString('documentCompliance', $output);
        $this->assertStringContainsString('EMP-INT-CMD-001', $output);
        $this->assertStringContainsString('intan.integration@mail.test', $output);
        $this->assertStringNotContainsString('private@mail.test', $output);
        $this->assertStringNotContainsString('08123456789', $output);
        $this->assertStringNotContainsString('SECRET-NIK', $output);
        $this->assertStringNotContainsString('Secret address', $output);
        $this->assertStringNotContainsString('confidential command note', $output);

        $this->assertSame($before, $employee->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_sample_command_rejects_invalid_inputs(): void
    {
        $this->artisan('hr:integration-contracts:sample', ['employeeId' => 999, '--date' => '18-07-2026'])
            ->assertFailed();

        $this->artisan('hr:integration-contracts:sample', ['employeeId' => 999, '--date' => '2026-02-30'])
            ->assertFailed();

        $this->artisan('hr:integration-contracts:sample', ['employeeId' => 999, '--date' => '2026-07-18', '--warning-days' => '-1'])
            ->assertFailed();

        $this->artisan('hr:integration-contracts:sample', ['employeeId' => 999, '--date' => '2026-07-18', '--warning-days' => '366'])
            ->assertFailed();

        $this->artisan('hr:integration-contracts:sample', ['employeeId' => 999, '--date' => '2026-07-18'])
            ->expectsOutputToContain('Employee not found for sample payload.')
            ->assertFailed();
    }
}
