<?php

namespace Tests\Feature;

use App\Modules\HR\IntegrationContracts\DTO\EmployeeAssignmentSnapshotV1;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeContractSnapshotV1;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeDocumentComplianceSnapshotV1;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeSnapshotV1;
use App\Modules\HR\IntegrationContracts\DTO\IntegrationEventEnvelopeV1;
use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class HRIntegrationEventPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_snapshot_v1_exposes_only_operational_identity_fields(): void
    {
        $snapshot = new EmployeeSnapshotV1(
            employeeId: 1001,
            employeeNumber: 'EMP-001',
            displayName: 'Budi Santoso',
            workEmail: 'budi@example.test',
            isActive: true,
            linkedUserId: 21,
        );

        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => 1001,
            'employeeNumber' => 'EMP-001',
            'displayName' => 'Budi Santoso',
            'workEmail' => 'budi@example.test',
            'isActive' => true,
            'linkedUserId' => 21,
        ], $snapshot->toArray());
    }

    public function test_assignment_contract_and_document_compliance_snapshots_are_allowlisted(): void
    {
        $assignment = new EmployeeAssignmentSnapshotV1(
            employeeId: 1001,
            effectiveDate: '2026-07-18',
            departement: ['id' => 1, 'code' => 'HR', 'name' => 'Human Resources'],
            position: ['id' => 5, 'code' => 'HR-OFFICER', 'name' => 'HR Officer'],
            jobLevel: ['id' => 2, 'code' => 'STAFF', 'name' => 'Staff'],
            workLocation: ['id' => 3, 'code' => 'HQ', 'name' => 'Head Office'],
            employmentStatus: ['id' => 1, 'code' => 'ACTIVE', 'name' => 'Active', 'requiresAttendance' => true, 'includedInPayroll' => true, 'isTerminal' => false],
            employmentType: ['id' => 1, 'code' => 'PERMANENT', 'name' => 'Permanent', 'requiresContractEndDate' => false, 'includedInPayroll' => true, 'eligibleForOvertime' => true],
        );

        $contract = new EmployeeContractSnapshotV1(
            employeeId: 1001,
            contractId: 88,
            contractType: 'PERMANENT',
            status: 'ACTIVE',
            startDate: '2026-01-01',
            endDate: null,
            isCurrent: true,
        );

        $compliance = new EmployeeDocumentComplianceSnapshotV1(
            employeeId: 1001,
            requiredCount: 4,
            verifiedCount: 3,
            pendingCount: 1,
            expiredCount: 0,
            expiringCount: 1,
            asOf: '2026-07-18',
        );

        $guard = new ForbiddenIntegrationFieldGuard;

        $this->assertSame([], $guard->forbiddenFieldsIn($assignment->toArray()));
        $this->assertSame([], $guard->forbiddenFieldsIn($contract->toArray()));
        $this->assertSame([], $guard->forbiddenFieldsIn($compliance->toArray()));

        $this->assertSame([
            'schemaVersion',
            'employeeId',
            'effectiveDate',
            'departement',
            'position',
            'jobLevel',
            'workLocation',
            'employmentStatus',
            'employmentType',
        ], array_keys($assignment->toArray()));

        $this->assertSame([
            'schemaVersion',
            'employeeId',
            'contractId',
            'contractType',
            'status',
            'startDate',
            'endDate',
            'isCurrent',
        ], array_keys($contract->toArray()));

        $this->assertSame([
            'schemaVersion',
            'employeeId',
            'requiredCount',
            'verifiedCount',
            'pendingCount',
            'expiredCount',
            'expiringCount',
            'asOf',
        ], array_keys($compliance->toArray()));
    }

    public function test_event_envelope_has_stable_shape_and_blocks_forbidden_payload_fields(): void
    {
        $envelope = new IntegrationEventEnvelopeV1(
            eventId: '7f61d530-3ab0-4c63-99ee-3870df15ca49',
            eventName: 'EmployeeAssignmentChangedV1',
            occurredAt: CarbonImmutable::parse('2026-07-18 10:00:00', 'Asia/Jakarta'),
            sourceModule: 'HR.EmployeeMovements',
            actorUserId: 12,
            correlationId: 'req-123',
            payload: [
                'employeeId' => 1001,
                'effectiveDate' => '2026-07-18',
                'changedFields' => ['positionId'],
            ],
        );

        $this->assertSame([
            'eventId' => '7f61d530-3ab0-4c63-99ee-3870df15ca49',
            'eventName' => 'EmployeeAssignmentChangedV1',
            'eventVersion' => 1,
            'occurredAt' => '2026-07-18T03:00:00Z',
            'sourceModule' => 'HR.EmployeeMovements',
            'actorUserId' => 12,
            'correlationId' => 'req-123',
            'payload' => [
                'employeeId' => 1001,
                'effectiveDate' => '2026-07-18',
                'changedFields' => ['positionId'],
            ],
        ], $envelope->toArray());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Integration payload contains forbidden field(s): password, dmsReference.');

        new IntegrationEventEnvelopeV1(
            eventId: '9b2173f5-7d9c-4702-bad8-f51193c20658',
            eventName: 'EmployeeDocumentComplianceChangedV1',
            occurredAt: CarbonImmutable::parse('2026-07-18 10:00:00', 'Asia/Jakarta'),
            sourceModule: 'HR.EmployeeDocuments',
            actorUserId: null,
            correlationId: null,
            payload: [
                'employeeId' => 1001,
                'password' => 'secret',
                'document' => [
                    'dmsReference' => 'dms_opaque_should_not_leave_here',
                ],
            ],
        );
    }

    public function test_forbidden_field_guard_detects_sensitive_keys_recursively(): void
    {
        $guard = new ForbiddenIntegrationFieldGuard;

        $payload = [
            'employeeId' => 1001,
            'profile' => [
                'nik' => '123',
                'bankAccount' => '999',
                'emergencyContactPhone' => '0812',
            ],
            'document' => [
                'documentNumber' => 'DOC-123',
                'storagePath' => '/private/doc.pdf',
                'downloadUrl' => 'https://example.test/private',
            ],
            'payroll' => [
                'salary' => 1000000,
                'compensation' => 1000000,
            ],
            'security' => [
                'rememberToken' => 'token',
                'resetPasswordToken' => 'token',
            ],
            'notesInternal' => 'secret notes',
        ];

        $this->assertSame([
            'nik',
            'bankAccount',
            'emergencyContactPhone',
            'documentNumber',
            'storagePath',
            'downloadUrl',
            'salary',
            'compensation',
            'rememberToken',
            'resetPasswordToken',
            'notesInternal',
        ], $guard->forbiddenFieldsIn($payload));
    }
}
