<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use App\Modules\HR\HRReports\Services\HeadcountReportService;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HRReportHeadcountTest extends TestCase
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

    public function test_headcount_groups_active_employees_by_departement_location_and_status(): void
    {
        $hr = $this->departement('HR', 'Human Resources');
        $ops = $this->departement('OPS', 'Operations');
        $hq = $this->workLocation('HQ', 'Head Office');
        $remote = $this->workLocation('REMOTE', 'Remote');
        $active = $this->employmentStatus('ACTIVE', 'Active');
        $probation = $this->employmentStatus('PROBATION', 'Probation');

        $this->employee('EMP-001', 'Ayu', $hr, $hq, $active, hiredAt: '2026-01-01');
        $this->employee('EMP-002', 'Bima', $hr, $remote, $probation, hiredAt: '2026-02-01');
        $this->employee('EMP-003', 'Citra', $ops, $hq, $active, hiredAt: '2026-03-01');
        $this->employee('EMP-004', 'Future', $ops, $remote, $active, hiredAt: '2026-08-01');
        $this->employee('EMP-005', 'Ended', $ops, $remote, $active, hiredAt: '2025-01-01', endedAt: '2026-06-30');
        $this->employee('EMP-006', 'Inactive', $ops, $remote, $active, active: false);
        $archived = $this->employee('EMP-007', 'Archived', $ops, $remote, $active);
        $archived->delete();

        $reports = app(HeadcountReportService::class);
        $filters = new HeadcountReportFilters(asOf: '2026-07-18');

        $this->assertSame([
            ['id' => $hr->id, 'code' => 'HR', 'name' => 'Human Resources', 'employeeCount' => 2],
            ['id' => $ops->id, 'code' => 'OPS', 'name' => 'Operations', 'employeeCount' => 1],
        ], $reports->byDepartement($filters)->rows);

        $this->assertSame([
            ['id' => $hq->id, 'code' => 'HQ', 'name' => 'Head Office', 'employeeCount' => 2],
            ['id' => $remote->id, 'code' => 'REMOTE', 'name' => 'Remote', 'employeeCount' => 1],
        ], $reports->byWorkLocation($filters)->rows);

        $this->assertSame([
            ['id' => $active->id, 'code' => 'ACTIVE', 'name' => 'Active', 'employeeCount' => 2],
            ['id' => $probation->id, 'code' => 'PROBATION', 'name' => 'Probation', 'employeeCount' => 1],
        ], $reports->byEmploymentStatus($filters)->rows);

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_headcount_supports_employment_status_filter_and_unknown_group(): void
    {
        $hr = $this->departement('HR', 'Human Resources');
        $hq = $this->workLocation('HQ', 'Head Office');
        $active = $this->employmentStatus('ACTIVE', 'Active');
        $probation = $this->employmentStatus('PROBATION', 'Probation');

        $this->employee('EMP-101', 'Active Employee', $hr, $hq, $active);
        $this->employee('EMP-102', 'Probation Employee', $hr, null, $probation);
        $this->employee('EMP-103', 'No Status Employee', null, null, null);

        $reports = app(HeadcountReportService::class);
        $filters = new HeadcountReportFilters(asOf: '2026-07-18', employmentStatusIds: [$probation->id]);

        $this->assertSame([
            ['id' => $hr->id, 'code' => 'HR', 'name' => 'Human Resources', 'employeeCount' => 1],
        ], $reports->byDepartement($filters)->rows);

        $this->assertSame([
            ['id' => null, 'code' => null, 'name' => 'Unassigned', 'employeeCount' => 1],
        ], $reports->byWorkLocation($filters)->rows);
    }

    public function test_hr_reports_page_returns_headcount_reports_with_explicit_filters(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');
        $hr = $this->departement('HR', 'Human Resources');
        $ops = $this->departement('OPS', 'Operations');
        $hq = $this->workLocation('HQ', 'Head Office');
        $active = $this->employmentStatus('ACTIVE', 'Active');
        $probation = $this->employmentStatus('PROBATION', 'Probation');

        $this->employee('EMP-201', 'Active One', $hr, $hq, $active, hiredAt: '2026-01-01');
        $this->employee('EMP-202', 'Probation One', $ops, $hq, $probation, hiredAt: '2026-01-01');

        $this->actingAs($user)
            ->get(route('hr.reports.index', [
                'as_of' => '2026-07-18',
                'employment_status_id' => $active->id,
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hr/hr-reports/index')
                ->where('filters.as_of', '2026-07-18')
                ->where('filters.employment_status_id', $active->id)
                ->where('headcount.byDepartement.total', 1)
                ->where('headcount.byDepartement.rows.0.name', 'Human Resources')
                ->where('headcount.byWorkLocation.total', 1)
                ->where('headcount.byEmploymentStatus.total', 1)
                ->has('options.employmentStatuses', 2)
            );

        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function departement(string $code, string $name): Departement
    {
        return Departement::query()->create([
            'code' => $code,
            'name' => $name,
            'active' => true,
        ]);
    }

    private function workLocation(string $code, string $name): WorkLocation
    {
        return WorkLocation::query()->create([
            'code' => $code,
            'name' => $name,
            'timezone' => 'Asia/Jakarta',
            'active' => true,
        ]);
    }

    private function employmentStatus(string $code, string $name): EmploymentStatus
    {
        return EmploymentStatus::query()->create([
            'code' => $code,
            'name' => $name,
            'requires_attendance' => true,
            'included_in_payroll' => true,
            'is_final_status' => false,
            'active' => true,
        ]);
    }

    private function employee(
        string $number,
        string $name,
        ?Departement $departement,
        ?WorkLocation $workLocation,
        ?EmploymentStatus $employmentStatus,
        string $hiredAt = '2026-01-01',
        ?string $endedAt = null,
        bool $active = true,
    ): Employee {
        return Employee::query()->create([
            'departement_id' => $departement?->id,
            'work_location_id' => $workLocation?->id,
            'employment_status_id' => $employmentStatus?->id,
            'employee_number' => $number,
            'first_name' => $name,
            'display_name' => $name,
            'hired_at' => $hiredAt,
            'ended_at' => $endedAt,
            'active' => $active,
        ]);
    }
}
