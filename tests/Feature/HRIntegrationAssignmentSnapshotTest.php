<?php

namespace Tests\Feature;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeAssignmentSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeAssignmentSnapshotV1;
use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HRIntegrationAssignmentSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_snapshot_provider_returns_current_work_profile_for_explicit_date(): void
    {
        Notification::fake();
        Queue::fake();

        $departement = Departement::query()->create(['code' => 'HR', 'name' => 'Human Resources', 'active' => true]);
        $position = Position::query()->create(['departement_id' => $departement->id, 'code' => 'HR-OFFICER', 'name' => 'HR Officer', 'active' => true]);
        $jobLevel = JobLevel::query()->create(['code' => 'STAFF', 'name' => 'Staff', 'active' => true]);
        $workLocation = WorkLocation::query()->create([
            'code' => 'HQ',
            'name' => 'Head Office',
            'address' => 'Private office address should not leak',
            'timezone' => 'Asia/Jakarta',
            'active' => true,
        ]);
        $employmentStatus = EmploymentStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'requires_attendance' => true,
            'included_in_payroll' => true,
            'is_final_status' => false,
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create([
            'code' => 'PERMANENT',
            'name' => 'Permanent',
            'requires_contract_end_date' => false,
            'included_in_payroll' => true,
            'eligible_for_benefits' => true,
            'eligible_for_overtime' => true,
            'active' => true,
        ]);

        $employee = Employee::query()->create([
            'departement_id' => $departement->id,
            'position_id' => $position->id,
            'job_level_id' => $jobLevel->id,
            'work_location_id' => $workLocation->id,
            'employment_status_id' => $employmentStatus->id,
            'employment_type_id' => $employmentType->id,
            'employee_number' => 'EMP-ASSIGNMENT',
            'first_name' => 'Assignment',
            'display_name' => 'Assignment Employee',
            'work_email' => 'assignment.employee@example.test',
            'address' => 'Private home address',
            'national_id' => 'NIK-SHOULD-NOT-LEAK',
            'notes' => 'Internal note',
            'active' => true,
        ]);

        $snapshot = app(EmployeeAssignmentSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');

        $this->assertInstanceOf(EmployeeAssignmentSnapshotV1::class, $snapshot);
        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'effectiveDate' => '2026-07-18',
            'departement' => ['id' => $departement->id, 'code' => 'HR', 'name' => 'Human Resources'],
            'position' => ['id' => $position->id, 'code' => 'HR-OFFICER', 'name' => 'HR Officer'],
            'jobLevel' => ['id' => $jobLevel->id, 'code' => 'STAFF', 'name' => 'Staff'],
            'workLocation' => ['id' => $workLocation->id, 'code' => 'HQ', 'name' => 'Head Office'],
            'employmentStatus' => [
                'id' => $employmentStatus->id,
                'code' => 'ACTIVE',
                'name' => 'Active',
                'requiresAttendance' => true,
                'includedInPayroll' => true,
                'isTerminal' => false,
            ],
            'employmentType' => [
                'id' => $employmentType->id,
                'code' => 'PERMANENT',
                'name' => 'Permanent',
                'requiresContractEndDate' => false,
                'includedInPayroll' => true,
                'eligibleForOvertime' => true,
            ],
        ], $snapshot->toArray());

        $this->assertSame([], app(ForbiddenIntegrationFieldGuard::class)->forbiddenFieldsIn($snapshot->toArray()));
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_assignment_snapshot_maps_terminal_status_for_payroll_and_attendance_consumers(): void
    {
        $terminalStatus = EmploymentStatus::query()->create([
            'code' => 'TERMINATED',
            'name' => 'Terminated',
            'requires_attendance' => false,
            'included_in_payroll' => false,
            'is_final_status' => true,
            'active' => true,
        ]);

        $employee = Employee::query()->create([
            'employment_status_id' => $terminalStatus->id,
            'employee_number' => 'EMP-TERMINAL',
            'first_name' => 'Terminal',
            'display_name' => 'Terminal Employee',
            'active' => false,
        ]);

        $snapshot = app(EmployeeAssignmentSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');

        $this->assertSame([
            'id' => $terminalStatus->id,
            'code' => 'TERMINATED',
            'name' => 'Terminated',
            'requiresAttendance' => false,
            'includedInPayroll' => false,
            'isTerminal' => true,
        ], $snapshot?->toArray()['employmentStatus']);
    }

    public function test_assignment_snapshot_provider_returns_null_for_missing_or_archived_employee(): void
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-ARCHIVED-ASSIGNMENT',
            'first_name' => 'Archived',
            'display_name' => 'Archived Assignment',
            'active' => true,
        ]);

        $employee->delete();

        $provider = app(EmployeeAssignmentSnapshotProvider::class);

        $this->assertNull($provider->forEmployee($employee->id, '2026-07-18'));
        $this->assertNull($provider->forEmployee(999999, '2026-07-18'));
    }

    public function test_assignment_snapshot_provider_does_not_write_files(): void
    {
        File::spy();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-ASSIGN-NO-FILE',
            'first_name' => 'No',
            'display_name' => 'No File Assignment',
            'active' => true,
        ]);

        $snapshot = app(EmployeeAssignmentSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');

        $this->assertSame('2026-07-18', $snapshot?->effectiveDate);
        File::shouldNotHaveReceived('put');
        File::shouldNotHaveReceived('append');
        File::shouldNotHaveReceived('delete');
    }
}
