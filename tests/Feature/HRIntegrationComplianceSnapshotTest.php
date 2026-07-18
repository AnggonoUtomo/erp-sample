<?php

namespace Tests\Feature;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeContractSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeDocumentComplianceSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeContractSnapshotV1;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeDocumentComplianceSnapshotV1;
use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HRIntegrationComplianceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_snapshot_provider_returns_safe_current_contract(): void
    {
        Notification::fake();
        Queue::fake();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-CONTRACT-SNAPSHOT',
            'first_name' => 'Contract',
            'display_name' => 'Contract Snapshot',
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create([
            'code' => 'PERMANENT',
            'name' => 'Permanent',
            'requires_contract_end_date' => false,
            'included_in_payroll' => true,
            'eligible_for_overtime' => true,
            'active' => true,
        ]);
        EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => 'CTR-SECRET-OLD',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => 'ENDED',
            'notes' => 'old confidential note',
        ]);
        $current = EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => 'CTR-SECRET-CURRENT',
            'start_date' => '2026-01-01',
            'end_date' => null,
            'status' => 'ACTIVE',
            'notes' => 'current confidential note',
        ]);

        $snapshot = app(EmployeeContractSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');

        $this->assertInstanceOf(EmployeeContractSnapshotV1::class, $snapshot);
        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'contractId' => $current->id,
            'contractType' => 'PERMANENT',
            'status' => 'ACTIVE',
            'startDate' => '2026-01-01',
            'endDate' => null,
            'isCurrent' => true,
        ], $snapshot->toArray());

        $this->assertSame([], app(ForbiddenIntegrationFieldGuard::class)->forbiddenFieldsIn($snapshot->toArray()));
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_contract_snapshot_provider_falls_back_to_latest_non_cancelled_contract(): void
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-CONTRACT-FALLBACK',
            'first_name' => 'Contract',
            'display_name' => 'Contract Fallback',
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create([
            'code' => 'CONTRACT',
            'name' => 'Contract',
            'requires_contract_end_date' => true,
            'included_in_payroll' => true,
            'eligible_for_overtime' => false,
            'active' => true,
        ]);
        $latest = EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => 'CTR-SECRET-LATEST',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'status' => 'DRAFT',
            'notes' => 'draft confidential note',
        ]);

        $snapshot = app(EmployeeContractSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');

        $this->assertSame($latest->id, $snapshot?->contractId);
        $this->assertSame('DRAFT', $snapshot?->status);
        $this->assertFalse($snapshot?->isCurrent);
    }

    public function test_document_compliance_snapshot_counts_status_and_expiry_without_sensitive_fields(): void
    {
        Notification::fake();
        Queue::fake();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-DOC-COMPLIANCE',
            'first_name' => 'Document',
            'display_name' => 'Document Compliance',
            'active' => true,
        ]);
        $documentType = ReferenceData::query()->create([
            'category' => 'EMPLOYEE_DOCUMENT_TYPE',
            'code' => 'KTP',
            'name' => 'KTP',
            'metadata' => ['requires_expiry' => true],
            'active' => true,
        ]);

        $this->document($employee, $documentType, 'DOC-VERIFIED', 'VERIFIED', '2027-12-31', 'dms_secret_verified');
        $this->document($employee, $documentType, 'DOC-PENDING', 'PENDING', '2026-08-01', 'dms_secret_pending');
        $this->document($employee, $documentType, 'DOC-EXPIRED', 'REJECTED', '2026-07-01', 'dms_secret_expired');

        $snapshot = app(EmployeeDocumentComplianceSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18', 30);

        $this->assertInstanceOf(EmployeeDocumentComplianceSnapshotV1::class, $snapshot);
        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'requiredCount' => 3,
            'verifiedCount' => 1,
            'pendingCount' => 1,
            'expiredCount' => 1,
            'expiringCount' => 1,
            'asOf' => '2026-07-18',
        ], $snapshot->toArray());

        $this->assertSame([], app(ForbiddenIntegrationFieldGuard::class)->forbiddenFieldsIn($snapshot->toArray()));
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_document_compliance_snapshot_returns_zeroes_for_missing_employee_or_no_documents(): void
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-DOC-EMPTY',
            'first_name' => 'Empty',
            'display_name' => 'Empty Document',
            'active' => true,
        ]);

        $snapshot = app(EmployeeDocumentComplianceSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18', 30);

        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'requiredCount' => 0,
            'verifiedCount' => 0,
            'pendingCount' => 0,
            'expiredCount' => 0,
            'expiringCount' => 0,
            'asOf' => '2026-07-18',
        ], $snapshot?->toArray());

        $this->assertNull(app(EmployeeDocumentComplianceSnapshotProvider::class)->forEmployee(999999, '2026-07-18', 30));
    }

    public function test_compliance_snapshot_providers_do_not_write_files(): void
    {
        File::spy();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-COMPLIANCE-NO-FILE',
            'first_name' => 'No',
            'display_name' => 'No File Compliance',
            'active' => true,
        ]);

        app(EmployeeContractSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18');
        app(EmployeeDocumentComplianceSnapshotProvider::class)->forEmployee($employee->id, '2026-07-18', 30);

        File::shouldNotHaveReceived('put');
        File::shouldNotHaveReceived('append');
        File::shouldNotHaveReceived('delete');
    }

    private function document(
        Employee $employee,
        ReferenceData $type,
        string $number,
        string $status,
        ?string $expiresAt,
        ?string $reference,
    ): EmployeeDocument {
        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'document_number' => $number,
            'document_number_fingerprint' => hash('sha256', $number),
            'document_number_uniqueness_key' => hash('sha256', $employee->id.'|'.$number),
            'issuer' => 'Secret Issuer',
            'issued_at' => '2026-01-01',
            'expires_at' => $expiresAt,
            'verification_status' => $status,
            'document_reference' => $reference,
            'document_reference_version' => $reference ? 1 : null,
            'notes' => 'confidential document note',
        ]);
    }
}
