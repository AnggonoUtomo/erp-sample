<?php

namespace Tests\Feature;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HRReportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_command_outputs_headcount_without_mutating_state(): void
    {
        Notification::fake();
        Queue::fake();

        $departement = Departement::query()->create(['code' => 'HR', 'name' => 'Human Resources', 'active' => true]);
        $location = WorkLocation::query()->create(['code' => 'HQ', 'name' => 'Head Office', 'timezone' => 'Asia/Jakarta', 'active' => true]);
        $status = EmploymentStatus::query()->create(['code' => 'ACTIVE', 'name' => 'Active', 'active' => true]);
        $employee = $this->employee('EMP-RPT-CMD-001', 'Rani Report', $departement, $location, $status);
        $before = $employee->fresh()->getAttributes();

        $this->artisan('hr:reports:summary', ['--date' => '2026-07-18'])
            ->expectsTable(
                ['Report', 'Group', 'Employee count'],
                [
                    ['Departement', 'Human Resources', 1],
                    ['Work Location', 'Head Office', 1],
                    ['Employment Status', 'Active', 1],
                ],
            )
            ->expectsOutputToContain('HR report summary for 2026-07-18.')
            ->assertSuccessful();

        $this->assertSame($before, $employee->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_contracts_expiring_command_uses_explicit_window_without_contract_number(): void
    {
        Notification::fake();
        Queue::fake();

        $employee = $this->employee('EMP-RPT-CMD-002', 'Sari Contract');
        $type = EmploymentType::query()->create(['code' => 'PKWT', 'name' => 'Fixed Term', 'active' => true]);
        $contract = EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $type->id,
            'contract_number' => 'SECRET-CONTRACT-CMD',
            'start_date' => '2026-01-01',
            'end_date' => '2026-07-20',
            'status' => 'ACTIVE',
            'notes' => 'contract command note',
        ]);
        $before = $contract->fresh()->getAttributes();

        $this->artisan('hr:reports:contracts-expiring', ['--date' => '2026-07-18', '--within' => '7'])
            ->expectsTable(
                ['Employee #', 'Employee', 'Employment type', 'Expiry date', 'State', 'Days remaining'],
                [['EMP-RPT-CMD-002', 'Sari Contract', 'Fixed Term', '2026-07-20', 'EXPIRING', 2]],
            )
            ->doesntExpectOutputToContain('SECRET-CONTRACT-CMD')
            ->doesntExpectOutputToContain('contract command note')
            ->expectsOutputToContain('1 contract expiry row(s) from 2026-07-18 through 2026-07-25.')
            ->assertSuccessful();

        $this->assertSame($before, $contract->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_documents_expiring_command_uses_explicit_window_without_sensitive_document_data(): void
    {
        Notification::fake();
        Queue::fake();

        $employee = $this->employee('EMP-RPT-CMD-003', 'Tari Document');
        $type = ReferenceData::query()->create([
            'category' => 'employee-document-type',
            'code' => 'PASSPORT',
            'name' => 'Passport',
            'metadata' => ['requires_expiry' => true, 'requires_number' => true, 'number_unique_scope' => 'EMPLOYEE'],
            'active' => true,
        ]);
        $document = EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'document_number' => 'SECRET-DOCUMENT-CMD',
            'expires_at' => '2026-07-20',
            'verification_status' => 'PENDING',
            'document_reference' => 'dms_secret_command',
            'document_reference_version' => 1,
            'notes' => 'document command note',
        ]);
        $before = $document->fresh()->getAttributes();

        $this->artisan('hr:reports:documents-expiring', ['--date' => '2026-07-18', '--within' => '7'])
            ->expectsTable(
                ['Employee #', 'Employee', 'Document type', 'Expiry date', 'State', 'Days remaining'],
                [['EMP-RPT-CMD-003', 'Tari Document', 'Passport', '2026-07-20', 'EXPIRING', 2]],
            )
            ->doesntExpectOutputToContain('SECRET-DOCUMENT-CMD')
            ->doesntExpectOutputToContain('dms_secret_command')
            ->doesntExpectOutputToContain('document command note')
            ->expectsOutputToContain('1 document expiry row(s) from 2026-07-18 through 2026-07-25.')
            ->assertSuccessful();

        $this->assertSame($before, $document->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_report_commands_reject_invalid_inputs_and_empty_results_are_successful(): void
    {
        $this->artisan('hr:reports:summary', ['--date' => '18-07-2026'])->assertFailed();
        $this->artisan('hr:reports:summary', ['--date' => '2026-02-30'])->assertFailed();
        $this->artisan('hr:reports:contracts-expiring', ['--date' => '2026-07-18', '--within' => '-1'])->assertFailed();
        $this->artisan('hr:reports:contracts-expiring', ['--date' => '2026-07-18', '--within' => '3651'])->assertFailed();
        $this->artisan('hr:reports:documents-expiring', ['--date' => '2026-07-18', '--within' => '-1'])->assertFailed();
        $this->artisan('hr:reports:documents-expiring', ['--date' => '2026-07-18', '--within' => '3651'])->assertFailed();

        $this->artisan('hr:reports:contracts-expiring', ['--date' => '2026-07-18', '--within' => '7'])
            ->expectsOutputToContain('No contract expiry rows from 2026-07-18 through 2026-07-25.')
            ->assertSuccessful();
        $this->artisan('hr:reports:documents-expiring', ['--date' => '2026-07-18', '--within' => '7'])
            ->expectsOutputToContain('No document expiry rows from 2026-07-18 through 2026-07-25.')
            ->assertSuccessful();
    }

    private function employee(
        string $number,
        string $name,
        ?Departement $departement = null,
        ?WorkLocation $workLocation = null,
        ?EmploymentStatus $employmentStatus = null,
    ): Employee {
        return Employee::query()->create([
            'departement_id' => $departement?->id,
            'work_location_id' => $workLocation?->id,
            'employment_status_id' => $employmentStatus?->id,
            'employee_number' => $number,
            'first_name' => $name,
            'display_name' => $name,
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
    }
}
