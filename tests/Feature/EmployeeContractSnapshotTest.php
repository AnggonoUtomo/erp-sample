<?php

namespace Tests\Feature;

use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractSnapshotReader;
use App\Modules\HR\EmployeeContracts\Integration\Projectors\EmployeeContractSnapshotProjector;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EmployeeContractSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_v1_has_exact_minimal_contract_at_inclusive_effective_date(): void
    {
        [$employee, $type] = $this->references();
        $contract = $this->contract($employee, $type, '2026-01-01', '2026-12-31', 'ACTIVE');
        $contract->update(['notes' => 'Private HR note']);

        $snapshot = app(EmployeeContractSnapshotProjector::class)->forEmployeeOn(
            $employee->id,
            CarbonImmutable::parse('2026-12-31'),
            CarbonImmutable::parse('2026-07-13T09:30:00Z'),
        );

        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'contractId' => $contract->id,
            'employmentTypeCode' => 'FIXED_TERM',
            'validFrom' => '2026-01-01',
            'validUntil' => '2026-12-31',
            'capturedAt' => '2026-07-13T09:30:00Z',
        ], $snapshot?->toArray());
    }

    public function test_snapshot_is_reproducible_after_current_employee_profile_changes(): void
    {
        [$employee, $type] = $this->references();
        $this->contract($employee, $type, '2026-01-01', null, 'ACTIVE');
        $projector = app(EmployeeContractSnapshotProjector::class);
        $effectiveDate = CarbonImmutable::parse('2026-07-13');
        $capturedAt = CarbonImmutable::parse('2026-07-13T09:30:00Z');

        $before = $projector->forEmployeeOn($employee->id, $effectiveDate, $capturedAt)?->toArray();
        $employee->update(['display_name' => 'Updated Current Profile', 'first_name' => 'Updated']);
        $after = $projector->forEmployeeOn($employee->id, $effectiveDate, $capturedAt)?->toArray();

        $this->assertSame($before, $after);
    }

    public function test_snapshot_excludes_draft_cancelled_and_archived_contracts(): void
    {
        [$employee, $type] = $this->references();
        $draft = $this->contract($employee, $type, '2026-01-01', '2026-04-30', 'DRAFT');
        $cancelled = $this->contract($employee, $type, '2026-05-01', '2026-08-31', 'CANCELLED');
        $archived = $this->contract($employee, $type, '2026-09-01', '2026-12-31', 'ACTIVE');
        $archived->delete();
        $projector = app(EmployeeContractSnapshotProjector::class);
        $capturedAt = CarbonImmutable::parse('2026-07-13T09:30:00Z');

        $this->assertNull($projector->forEmployeeOn($employee->id, CarbonImmutable::parse('2026-02-01'), $capturedAt));
        $this->assertNull($projector->forEmployeeOn($employee->id, CarbonImmutable::parse('2026-07-13'), $capturedAt));
        $this->assertNull($projector->forEmployeeOn($employee->id, CarbonImmutable::parse('2026-10-01'), $capturedAt));
        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $draft->id]);
        $this->assertDatabaseHas('hr_employee_contracts', ['id' => $cancelled->id]);
    }

    public function test_snapshot_schema_v1_declares_only_the_published_fields(): void
    {
        $schema = File::json(app_path('Modules/HR/EmployeeContracts/Integration/Schemas/employee-contract-snapshot-v1.json'));

        $this->assertSame(
            ['schemaVersion', 'employeeId', 'contractId', 'employmentTypeCode', 'validFrom', 'validUntil', 'capturedAt'],
            $schema['required'],
        );
        $this->assertSame($schema['required'], array_keys($schema['properties']));
        $this->assertFalse($schema['additionalProperties']);

        $manifest = require app_path('Modules/HR/EmployeeContracts/module.php');
        $snapshotContract = collect($manifest['integrations']['contracts'])
            ->firstWhere('name', 'EmployeeContractSnapshot');
        $this->assertSame([
            'name' => 'EmployeeContractSnapshot',
            'schema_version' => 1,
            'reader' => EmployeeContractSnapshotReader::class,
            'schema' => 'Integration/Schemas/employee-contract-snapshot-v1.json',
        ], $snapshotContract);
        $this->assertInstanceOf(EmployeeContractSnapshotProjector::class, app(EmployeeContractSnapshotReader::class));
    }

    /** @return array{Employee, EmploymentType} */
    private function references(): array
    {
        $status = EmploymentStatus::query()->create([
            'code' => 'ACTIVE', 'name' => 'Active', 'requires_attendance' => true,
            'included_in_payroll' => true, 'is_final_status' => false, 'active' => true,
        ]);
        $type = EmploymentType::query()->create([
            'code' => 'FIXED_TERM', 'name' => 'Fixed Term', 'requires_contract_end_date' => true,
            'included_in_payroll' => true, 'eligible_for_benefits' => true,
            'eligible_for_overtime' => true, 'active' => true,
        ]);
        $employee = Employee::query()->create([
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id,
            'employee_number' => 'EMP-SNAPSHOT', 'first_name' => 'Snapshot',
            'display_name' => 'Snapshot Employee', 'active' => true,
        ]);

        return [$employee, $type];
    }

    private function contract(Employee $employee, EmploymentType $type, string $start, ?string $end, string $status): EmployeeContract
    {
        return EmployeeContract::query()->create([
            'employee_id' => $employee->id, 'employment_type_id' => $type->id,
            'contract_number' => 'SNAP-'.str_replace('-', '', $start),
            'start_date' => $start, 'end_date' => $end, 'status' => $status,
        ]);
    }
}
