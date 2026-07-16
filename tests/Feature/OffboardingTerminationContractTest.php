<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Integration\Adapters\EloquentEmployeeContractTerminationAdapter;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractTerminationGateway;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationCommandV1;
use App\Modules\HR\EmployeeContracts\Integration\Exceptions\EmployeeContractTerminationRejected;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Integration\Adapters\EloquentEmployeeTerminationAdapter;
use App\Modules\HR\Employees\Integration\Contracts\EmployeeTerminationGateway;
use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationCommandV1;
use App\Modules\HR\Employees\Integration\Exceptions\EmployeeTerminationRejected;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery\MockInterface;
use Tests\TestCase;

class OffboardingTerminationContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_modules_publish_versioned_model_free_contracts_and_bindings(): void
    {
        $employeeCommand = new EmployeeTerminationCommandV1(
            employeeId: 41,
            expectedState: 'ACTIVE',
            targetEmploymentStatusId: 7,
            effectiveDate: '2026-07-20',
            reason: 'Approved offboarding.',
            actorUserId: 9,
        );
        $contractCommand = new EmployeeContractTerminationCommandV1(
            contractId: 81,
            employeeId: 41,
            expectedState: 'ACTIVE',
            effectiveDate: '2026-07-20',
            reason: 'Approved offboarding.',
            actorUserId: 9,
        );

        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => 41,
            'expectedState' => 'ACTIVE',
            'targetEmploymentStatusId' => 7,
            'effectiveDate' => '2026-07-20',
            'reason' => 'Approved offboarding.',
            'actorUserId' => 9,
        ], $employeeCommand->toArray());
        $this->assertSame([
            'schemaVersion' => 1,
            'contractId' => 81,
            'employeeId' => 41,
            'expectedState' => 'ACTIVE',
            'effectiveDate' => '2026-07-20',
            'reason' => 'Approved offboarding.',
            'actorUserId' => 9,
        ], $contractCommand->toArray());
        $this->assertInstanceOf(EloquentEmployeeTerminationAdapter::class, app(EmployeeTerminationGateway::class));
        $this->assertInstanceOf(EloquentEmployeeContractTerminationAdapter::class, app(EmployeeContractTerminationGateway::class));

        $employeesManifest = require app_path('Modules/HR/Employees/module.php');
        $contractsManifest = require app_path('Modules/HR/EmployeeContracts/module.php');
        $employeeContract = collect($employeesManifest['integrations']['contracts'])->firstWhere('name', 'EmployeeTerminationGateway');
        $contractContract = collect($contractsManifest['integrations']['contracts'])->firstWhere('name', 'EmployeeContractTerminationGateway');

        $this->assertSame(1, $employeeContract['schema_version']);
        $this->assertSame(EmployeeTerminationGateway::class, $employeeContract['reader']);
        $this->assertSame(1, $contractContract['schema_version']);
        $this->assertSame(EmployeeContractTerminationGateway::class, $contractContract['reader']);

        foreach ([
            app_path('Modules/HR/Employees/Integration/Schemas/employee-termination-command-v1.json'),
            app_path('Modules/HR/EmployeeContracts/Integration/Schemas/employee-contract-termination-command-v1.json'),
        ] as $schemaPath) {
            $schema = File::json($schemaPath);
            $this->assertSame(array_keys($schema['properties']), $schema['required']);
            $this->assertFalse($schema['additionalProperties']);
        }

        foreach ([
            app_path('Modules/HR/Employees/Integration/Contracts/EmployeeTerminationGateway.php'),
            app_path('Modules/HR/Employees/Integration/DTO/EmployeeTerminationCommandV1.php'),
            app_path('Modules/HR/Employees/Integration/DTO/EmployeeTerminationResultV1.php'),
            app_path('Modules/HR/EmployeeContracts/Integration/Contracts/EmployeeContractTerminationGateway.php'),
            app_path('Modules/HR/EmployeeContracts/Integration/DTO/EmployeeContractTerminationCommandV1.php'),
            app_path('Modules/HR/EmployeeContracts/Integration/DTO/EmployeeContractTerminationResultV1.php'),
        ] as $publicPath) {
            $contents = file_get_contents($publicPath);
            $this->assertStringNotContainsString('\\Models\\', $contents);
            $this->assertStringNotContainsString('\\Services\\', $contents);
        }
    }

    public function test_employee_gateway_terminates_only_active_non_archived_employee_to_active_final_status(): void
    {
        $actor = User::factory()->create();
        [$employee, $activeStatus, $finalStatus] = $this->employeeReferences();
        $result = app(EmployeeTerminationGateway::class)->terminate(new EmployeeTerminationCommandV1(
            employeeId: $employee->id,
            expectedState: 'ACTIVE',
            targetEmploymentStatusId: $finalStatus->id,
            effectiveDate: '2026-07-20',
            reason: 'Employment ended through approved offboarding.',
            actorUserId: $actor->id,
        ));

        $employee->refresh();
        $this->assertFalse($employee->active);
        $this->assertSame('2026-07-20', $employee->ended_at?->toDateString());
        $this->assertSame($finalStatus->id, $employee->employment_status_id);
        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'previousEmploymentStatusId' => $activeStatus->id,
            'employmentStatusId' => $finalStatus->id,
            'effectiveDate' => '2026-07-20',
            'state' => 'TERMINATED',
        ], $result->toArray());
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $actor->id,
            'event' => 'Employee.terminated-via-boundary',
            'auditable_id' => $employee->id,
        ]);
    }

    public function test_employee_gateway_rejects_archived_stale_terminal_invalid_target_and_actor(): void
    {
        [$employee, $activeStatus, $finalStatus] = $this->employeeReferences();
        $actor = User::factory()->create();
        $gateway = app(EmployeeTerminationGateway::class);

        foreach (range(0, 4) as $index) {
            [$caseEmployee, $caseActiveStatus, $caseFinalStatus] = $index === 0
                ? [$employee, $activeStatus, $finalStatus]
                : $this->employeeReferences("EMP-TERM-{$index}");
            $caseActor = $index === 4 ? $actor : User::factory()->create();
            if ($index > 0) {
                $employee = $caseEmployee;
                $activeStatus = $caseActiveStatus;
                $finalStatus = $caseFinalStatus;
                $actor = $caseActor;
            }
            $mutate = match ($index) {
                0 => fn () => $caseEmployee->delete(),
                1 => fn () => $caseEmployee->update(['active' => false]),
                2 => fn () => $caseEmployee->update(['ended_at' => '2026-07-19']),
                3 => fn () => $caseFinalStatus->update(['active' => false]),
                4 => fn () => $caseActor->delete(),
            };
            $mutate();

            try {
                $gateway->terminate(new EmployeeTerminationCommandV1(
                    employeeId: $caseEmployee->id,
                    expectedState: 'ACTIVE',
                    targetEmploymentStatusId: $caseFinalStatus->id,
                    effectiveDate: '2026-07-20',
                    reason: 'Rejected boundary call.',
                    actorUserId: $caseActor->id,
                ));
                $this->fail("Employee termination case {$index} was accepted.");
            } catch (EmployeeTerminationRejected) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_contract_gateway_terminates_matching_active_contract_and_rejects_stale_resources(): void
    {
        $actor = User::factory()->create();
        [$employee] = $this->employeeReferences('EMP-CONTRACT-BOUNDARY');
        $contract = $this->contract($employee);
        $gateway = app(EmployeeContractTerminationGateway::class);
        $result = $gateway->terminate(new EmployeeContractTerminationCommandV1(
            contractId: $contract->id,
            employeeId: $employee->id,
            expectedState: 'ACTIVE',
            effectiveDate: '2026-07-20',
            reason: 'Contract ended through approved offboarding.',
            actorUserId: $actor->id,
        ));

        $contract->refresh();
        $this->assertSame('ENDED', $contract->status);
        $this->assertSame('2026-07-20', $contract->end_date?->toDateString());
        $this->assertSame('Contract ended through approved offboarding.', $contract->ended_reason);
        $this->assertSame([
            'schemaVersion' => 1,
            'contractId' => $contract->id,
            'employeeId' => $employee->id,
            'effectiveDate' => '2026-07-20',
            'state' => 'ENDED',
        ], $result->toArray());

        foreach (['archived', 'wrong_employee', 'terminal', 'before_start'] as $case) {
            [$caseEmployee] = $this->employeeReferences('EMP-CONTRACT-'.strtoupper($case));
            $caseContract = $this->contract($caseEmployee);
            $caseActor = User::factory()->create();
            if ($case === 'archived') {
                $caseContract->delete();
            } elseif ($case === 'terminal') {
                $caseContract->update(['status' => 'ENDED']);
            }

            try {
                $gateway->terminate(new EmployeeContractTerminationCommandV1(
                    contractId: $caseContract->id,
                    employeeId: $case === 'wrong_employee' ? $caseEmployee->id + 999 : $caseEmployee->id,
                    expectedState: 'ACTIVE',
                    effectiveDate: $case === 'before_start' ? '2025-12-31' : '2026-07-20',
                    reason: 'Rejected contract boundary call.',
                    actorUserId: $caseActor->id,
                ));
                $this->fail("Contract termination case {$case} was accepted.");
            } catch (EmployeeContractTerminationRejected) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_owner_gateway_audit_failure_rolls_back_mutation(): void
    {
        $actor = User::factory()->create();
        [$employee, , $finalStatus] = $this->employeeReferences('EMP-TERM-ROLLBACK');
        $this->mock(AuditLogService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->expectException(\RuntimeException::class);
        try {
            app(EmployeeTerminationGateway::class)->terminate(new EmployeeTerminationCommandV1(
                employeeId: $employee->id,
                expectedState: 'ACTIVE',
                targetEmploymentStatusId: $finalStatus->id,
                effectiveDate: '2026-07-20',
                reason: 'Rollback.',
                actorUserId: $actor->id,
            ));
        } finally {
            $employee->refresh();
            $this->assertTrue($employee->active);
            $this->assertNull($employee->ended_at);
        }
    }

    /** @return array{Employee, EmploymentStatus, EmploymentStatus} */
    private function employeeReferences(string $employeeNumber = 'EMP-TERM-BOUNDARY'): array
    {
        $activeStatus = EmploymentStatus::query()->create([
            'code' => 'ACTIVE-'.uniqid(),
            'name' => 'Active',
            'is_final_status' => false,
            'active' => true,
        ]);
        $finalStatus = EmploymentStatus::query()->create([
            'code' => 'ENDED-'.uniqid(),
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $employee = Employee::query()->create([
            'employment_status_id' => $activeStatus->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Boundary',
            'display_name' => "Boundary {$employeeNumber}",
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);

        return [$employee, $activeStatus, $finalStatus];
    }

    private function contract(Employee $employee): EmployeeContract
    {
        $type = EmploymentType::query()->firstOrCreate(
            ['code' => 'PERM-BOUNDARY'],
            ['name' => 'Permanent'],
        );

        return EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $type->id,
            'contract_number' => 'BOUNDARY-'.$employee->id,
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
    }
}
