<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\HRReports\Database\Seeders\HRReportLifecycleSeeder;
use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use App\Modules\HR\HRReports\Services\ContractExpiryReportService;
use App\Modules\HR\HRReports\Services\DocumentExpiryReportService;
use App\Modules\HR\HRReports\Services\HeadcountReportService;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Onboardings\Models\Onboarding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HRReportLifecycleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_lifecycle_report_seeder_is_idempotent_and_populates_mvp_reports(): void
    {
        $this->seed(HRReportLifecycleSeeder::class);

        $countsAfterFirstRun = $this->seededCounts();

        $this->seed(HRReportLifecycleSeeder::class);

        $this->assertSame($countsAfterFirstRun, $this->seededCounts());

        $this->assertSame(3, Employee::query()->where('employee_number', 'like', 'EMP-RPT-%')->count());
        $this->assertGreaterThanOrEqual(2, EmployeeContract::query()->where('contract_number', 'like', 'RPT-CONTRACT-%')->count());
        $this->assertGreaterThanOrEqual(2, EmployeeDocument::query()->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))->count());
        $this->assertGreaterThanOrEqual(1, EmployeeMovement::query()->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))->count());
        $this->assertGreaterThanOrEqual(1, Onboarding::query()->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))->count());
        $this->assertGreaterThanOrEqual(1, Offboarding::query()->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))->count());

        $this->assertGreaterThan(0, app(HeadcountReportService::class)
            ->byDepartement(new HeadcountReportFilters(asOf: '2026-07-18'))
            ->total);
        $this->assertGreaterThan(0, app(HeadcountReportService::class)
            ->byWorkLocation(new HeadcountReportFilters(asOf: '2026-07-18'))
            ->total);
        $this->assertGreaterThan(0, app(HeadcountReportService::class)
            ->byEmploymentStatus(new HeadcountReportFilters(asOf: '2026-07-18'))
            ->total);
        $this->assertGreaterThan(0, app(ContractExpiryReportService::class)
            ->expiring(new ExpiryReportFilters(asOf: '2026-07-18', withinDays: 30))
            ->total);
        $this->assertGreaterThan(0, app(DocumentExpiryReportService::class)
            ->expiring(new ExpiryReportFilters(asOf: '2026-07-18', withinDays: 30))
            ->total);

        $this->assertDatabaseHas('users', ['email' => 'hr.report.lifecycle@example.test']);
        $this->assertSame(
            3,
            Employee::query()
                ->where('employee_number', 'like', 'EMP-RPT-%')
                ->where(fn ($query) => $query->whereNull('work_email')->orWhere('work_email', 'like', '%@example.test'))
                ->count(),
        );
        $this->assertSame(0, EmployeeDocument::query()
            ->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))
            ->whereNotNull('document_reference')
            ->count());
    }

    /**
     * @return array<string, int>
     */
    private function seededCounts(): array
    {
        return [
            'users' => User::query()->where('email', 'hr.report.lifecycle@example.test')->count(),
            'employees' => Employee::query()->where('employee_number', 'like', 'EMP-RPT-%')->count(),
            'contracts' => EmployeeContract::query()->where('contract_number', 'like', 'RPT-CONTRACT-%')->count(),
            'documents' => EmployeeDocument::query()
                ->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))
                ->count(),
            'movements' => EmployeeMovement::query()
                ->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))
                ->count(),
            'onboardings' => Onboarding::query()
                ->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))
                ->count(),
            'offboardings' => Offboarding::query()
                ->whereHas('employee', fn ($query) => $query->where('employee_number', 'like', 'EMP-RPT-%'))
                ->count(),
        ];
    }
}
