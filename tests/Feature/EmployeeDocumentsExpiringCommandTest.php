<?php

namespace Tests\Feature;

use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmployeeDocumentsExpiringCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HRReferenceDataSeeder::class);
    }

    public function test_command_lists_active_metadata_in_deterministic_inclusive_window_without_number(): void
    {
        $today = $this->document('2026-07-13', 'SECRET-TODAY');
        $boundary = $this->document('2026-08-12', 'SECRET-BOUNDARY');
        $this->document('2026-08-13', 'SECRET-OUTSIDE');
        $archived = $this->document('2026-07-20', 'SECRET-ARCHIVED');
        $archived->delete();

        $this->artisan('hr:documents-expiring', ['--date' => '2026-07-13', '--within' => '30'])
            ->expectsTable(
                ['Metadata ID', 'Employee', 'Document type', 'Expiry date'],
                [
                    [$today->id, 'Command Employee', 'Passport', '2026-07-13'],
                    [$boundary->id, 'Command Employee', 'Passport', '2026-08-12'],
                ],
            )
            ->doesntExpectOutputToContain('SECRET-')
            ->expectsOutputToContain('2 document(s) expiring from 2026-07-13 through 2026-08-12.')
            ->assertSuccessful();
    }

    public function test_command_is_read_only_and_empty_result_is_success(): void
    {
        Notification::fake();
        $document = $this->document('2027-12-31', 'READ-ONLY');
        $before = $document->fresh()->getAttributes();

        $this->artisan('hr:documents-expiring', ['--date' => '2026-07-13', '--within' => '7'])
            ->expectsOutputToContain('No documents expiring from 2026-07-13 through 2026-07-20.')
            ->assertSuccessful();

        $this->assertSame($before, $document->fresh()->getAttributes());
        $this->assertDatabaseCount('audit_logs', 0);
        Notification::assertNothingSent();
    }

    public function test_command_rejects_invalid_date_and_window(): void
    {
        $this->artisan('hr:documents-expiring', ['--date' => '13-07-2026'])->assertFailed();
        $this->artisan('hr:documents-expiring', ['--date' => '2026-02-30'])->assertFailed();
        $this->artisan('hr:documents-expiring', ['--date' => '2026-07-13', '--within' => '-1'])->assertFailed();
        $this->artisan('hr:documents-expiring', ['--date' => '2026-07-13', '--within' => '3651'])->assertFailed();
    }

    private function document(string $expiresAt, string $number): EmployeeDocument
    {
        $status = EmploymentStatus::query()->firstOrCreate(['code' => 'ACTIVE'], ['name' => 'Active', 'active' => true]);
        $employmentType = EmploymentType::query()->firstOrCreate(['code' => 'PERM'], ['name' => 'Permanent', 'active' => true]);
        $employee = Employee::query()->firstOrCreate(['employee_number' => 'EMP-COMMAND'], [
            'first_name' => 'Command', 'display_name' => 'Command Employee',
            'employment_status_id' => $status->id, 'employment_type_id' => $employmentType->id, 'active' => true,
        ]);
        $type = ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'PASSPORT')->firstOrFail();

        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id, 'document_type_id' => $type->id,
            'document_number' => $number, 'expires_at' => $expiresAt, 'verification_status' => 'PENDING',
        ]);
    }
}
