<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\Services\DocumentExpiryReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HRReportDocumentExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach (['hr.view', 'hr-reports.view'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_document_expiry_report_returns_expired_and_expiring_metadata_without_sensitive_fields(): void
    {
        $type = $this->documentType('PASSPORT', 'Passport');
        $employee = $this->employee('EMP-DEXP-001', 'Dewi Document');
        $expired = $this->document($employee, $type, 'DEXP-EXPIRED-SECRET', '2026-07-10', reference: 'dms_secret_expired');
        $expiringToday = $this->document($employee, $type, 'DEXP-TODAY-SECRET', '2026-07-18', reference: 'dms_secret_today');
        $expiring = $this->document($employee, $type, 'DEXP-SOON-SECRET', '2026-08-10', reference: 'dms_secret_soon');
        $this->document($employee, $type, 'DEXP-LATER-SECRET', '2026-09-01', reference: 'dms_secret_later');
        $this->document($employee, $type, 'DEXP-NOT-APPLICABLE-SECRET', null, reference: 'dms_secret_null');
        $archived = $this->document($employee, $type, 'DEXP-ARCHIVED-SECRET', '2026-07-26', reference: 'dms_secret_archived');
        $archived->delete();

        $report = app(DocumentExpiryReportService::class)
            ->expiring(new ExpiryReportFilters(asOf: '2026-07-18', withinDays: 30));

        $this->assertSame('2026-07-18', $report->asOf);
        $this->assertSame(30, $report->withinDays);
        $this->assertSame(3, $report->total);
        $this->assertSame([
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-DEXP-001',
                'employeeName' => 'Dewi Document',
                'typeLabel' => 'Passport',
                'expiresAt' => '2026-07-10',
                'daysRemaining' => -8,
                'state' => 'EXPIRED',
            ],
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-DEXP-001',
                'employeeName' => 'Dewi Document',
                'typeLabel' => 'Passport',
                'expiresAt' => '2026-07-18',
                'daysRemaining' => 0,
                'state' => 'EXPIRING',
            ],
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-DEXP-001',
                'employeeName' => 'Dewi Document',
                'typeLabel' => 'Passport',
                'expiresAt' => '2026-08-10',
                'daysRemaining' => 23,
                'state' => 'EXPIRING',
            ],
        ], $report->rows);

        $serialized = json_encode($report->rows, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($expired->document_number, $serialized);
        $this->assertStringNotContainsString($expiringToday->document_number, $serialized);
        $this->assertStringNotContainsString($expiring->document_reference, $serialized);
        $this->assertStringNotContainsString('dms_secret', $serialized);
        $this->assertStringNotContainsString('storage', $serialized);
        $this->assertStringNotContainsString('token', $serialized);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_document_expiry_report_page_accepts_explicit_window_filter(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');

        $type = $this->documentType('VISA', 'Work Visa');
        $employee = $this->employee('EMP-DEXP-101', 'Eka Document');
        $this->document($employee, $type, 'DEXP-PAGE-SECRET', '2026-07-20', reference: 'dms_secret_page');

        $this->actingAs($user)
            ->get(route('hr.reports.index', [
                'as_of' => '2026-07-18',
                'document_within_days' => 7,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hr/hr-reports/index')
                ->where('filters.document_within_days', 7)
                ->where('documentExpiry.asOf', '2026-07-18')
                ->where('documentExpiry.withinDays', 7)
                ->where('documentExpiry.total', 1)
                ->where('documentExpiry.rows.0.employeeName', 'Eka Document')
                ->where('documentExpiry.rows.0.typeLabel', 'Work Visa')
                ->where('documentExpiry.rows.0.daysRemaining', 2)
                ->where('documentExpiry.rows.0.state', 'EXPIRING')
                ->missing('documentExpiry.rows.0.documentNumber')
                ->missing('documentExpiry.rows.0.documentReference')
                ->missing('documentExpiry.rows.0.url')
                ->missing('documentExpiry.rows.0.token')
            );

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_invalid_document_expiry_window_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');

        $this->actingAs($user)
            ->get(route('hr.reports.index', ['document_within_days' => 3651]))
            ->assertSessionHasErrors('document_within_days');
    }

    private function employee(string $number, string $name): Employee
    {
        return Employee::query()->create([
            'employee_number' => $number,
            'first_name' => $name,
            'display_name' => $name,
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
    }

    private function documentType(string $code, string $name): ReferenceData
    {
        return ReferenceData::query()->create([
            'category' => 'employee-document-type',
            'code' => $code,
            'name' => $name,
            'metadata' => [
                'requires_expiry' => true,
                'requires_number' => true,
                'number_unique_scope' => 'EMPLOYEE',
            ],
            'active' => true,
        ]);
    }

    private function document(
        Employee $employee,
        ReferenceData $documentType,
        string $number,
        ?string $expiresAt,
        ?string $reference = null,
    ): EmployeeDocument {
        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => $documentType->id,
            'document_number' => $number,
            'expires_at' => $expiresAt,
            'verification_status' => 'PENDING',
            'document_reference' => $reference,
            'document_reference_version' => $reference === null ? null : 1,
            'notes' => 'internal document note',
        ]);
    }
}
