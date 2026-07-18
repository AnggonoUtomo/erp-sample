<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\Services\ContractExpiryReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HRReportContractExpiryTest extends TestCase
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

    public function test_contract_expiry_report_returns_active_expired_and_expiring_contracts_without_sensitive_fields(): void
    {
        $type = $this->employmentType('PKWT', 'Fixed Term');
        $employee = $this->employee('EMP-CEXP-001', 'Ayu Contract');
        $expired = $this->contract($employee, $type, 'CEXP-EXPIRED', '2026-07-10', notes: 'internal expired note');
        $expiringToday = $this->contract($employee, $type, 'CEXP-TODAY', '2026-07-18', notes: 'internal today note');
        $expiring = $this->contract($employee, $type, 'CEXP-SOON', '2026-08-10', notes: 'internal soon note');
        $this->contract($employee, $type, 'CEXP-LATER', '2026-09-01');
        $this->contract($employee, $type, 'CEXP-DRAFT', '2026-07-25', status: 'DRAFT');
        $archived = $this->contract($employee, $type, 'CEXP-ARCHIVED', '2026-07-26');
        $archived->delete();

        $report = app(ContractExpiryReportService::class)
            ->expiring(new ExpiryReportFilters(asOf: '2026-07-18', withinDays: 30));

        $this->assertSame('2026-07-18', $report->asOf);
        $this->assertSame(30, $report->withinDays);
        $this->assertSame(3, $report->total);
        $this->assertSame([
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-CEXP-001',
                'employeeName' => 'Ayu Contract',
                'typeLabel' => 'Fixed Term',
                'expiresAt' => '2026-07-10',
                'daysRemaining' => -8,
                'state' => 'EXPIRED',
            ],
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-CEXP-001',
                'employeeName' => 'Ayu Contract',
                'typeLabel' => 'Fixed Term',
                'expiresAt' => '2026-07-18',
                'daysRemaining' => 0,
                'state' => 'EXPIRING',
            ],
            [
                'employeeId' => $employee->id,
                'employeeNumber' => 'EMP-CEXP-001',
                'employeeName' => 'Ayu Contract',
                'typeLabel' => 'Fixed Term',
                'expiresAt' => '2026-08-10',
                'daysRemaining' => 23,
                'state' => 'EXPIRING',
            ],
        ], $report->rows);

        $serialized = json_encode($report->rows, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString($expired->contract_number, $serialized);
        $this->assertStringNotContainsString($expiringToday->notes, $serialized);
        $this->assertStringNotContainsString($expiring->notes, $serialized);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_contract_expiry_report_page_accepts_explicit_window_filter(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');

        $type = $this->employmentType('CONTRACT', 'Contract');
        $employee = $this->employee('EMP-CEXP-101', 'Bima Contract');
        $this->contract($employee, $type, 'CEXP-PAGE', '2026-07-20', notes: 'page internal note');

        $this->actingAs($user)
            ->get(route('hr.reports.index', [
                'as_of' => '2026-07-18',
                'contract_within_days' => 7,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hr/hr-reports/index')
                ->where('filters.contract_within_days', 7)
                ->where('contractExpiry.asOf', '2026-07-18')
                ->where('contractExpiry.withinDays', 7)
                ->where('contractExpiry.total', 1)
                ->where('contractExpiry.rows.0.employeeName', 'Bima Contract')
                ->where('contractExpiry.rows.0.daysRemaining', 2)
                ->where('contractExpiry.rows.0.state', 'EXPIRING')
                ->missing('contractExpiry.rows.0.contractNumber')
                ->missing('contractExpiry.rows.0.notes')
            );

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_invalid_contract_expiry_window_is_rejected(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');

        $this->actingAs($user)
            ->get(route('hr.reports.index', ['contract_within_days' => 3651]))
            ->assertSessionHasErrors('contract_within_days');
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

    private function employmentType(string $code, string $name): EmploymentType
    {
        return EmploymentType::query()->create([
            'code' => $code,
            'name' => $name,
            'requires_contract_end_date' => true,
            'included_in_payroll' => true,
            'active' => true,
        ]);
    }

    private function contract(
        Employee $employee,
        EmploymentType $employmentType,
        string $number,
        string $endDate,
        string $status = 'ACTIVE',
        ?string $notes = null,
    ): EmployeeContract {
        return EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => $number,
            'start_date' => '2026-01-01',
            'end_date' => $endDate,
            'status' => $status,
            'notes' => $notes,
        ]);
    }
}
