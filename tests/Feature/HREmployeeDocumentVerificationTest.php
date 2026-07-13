<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeDocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Permission::findOrCreate('employee-documents.verify');
        $this->seed(HRReferenceDataSeeder::class);
    }

    public function test_verifier_can_verify_pending_document_with_actor_timestamp_and_audit(): void
    {
        $document = $this->document();
        $verifier = User::factory()->create();
        $verifier->givePermissionTo('employee-documents.verify');

        $this->actingAs($verifier)->post(route('hr.employee-documents.verify', $document), [
            'reason' => 'Dokumen asli telah diperiksa.',
        ])->assertRedirect()->assertSessionDoesntHaveErrors();

        $document->refresh();
        $this->assertSame('VERIFIED', $document->verification_status);
        $this->assertSame($verifier->id, $document->verified_by);
        $this->assertNotNull($document->verified_at);
        $this->assertSame('Dokumen asli telah diperiksa.', $document->verification_reason);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'EmployeeDocument.verified', 'actor_id' => $verifier->id,
            'auditable_id' => $document->id,
        ]);
    }

    public function test_reject_requires_reason_and_records_reviewer(): void
    {
        $document = $this->document();
        $verifier = User::factory()->create();
        $verifier->givePermissionTo('employee-documents.verify');

        $this->actingAs($verifier)->post(route('hr.employee-documents.reject', $document), ['reason' => ' '])
            ->assertSessionHasErrors('reason');
        $this->assertSame('PENDING', $document->refresh()->verification_status);
        $this->assertDatabaseMissing('audit_logs', ['event' => 'EmployeeDocument.rejected']);

        $this->actingAs($verifier)->post(route('hr.employee-documents.reject', $document), [
            'reason' => 'Nomor tidak sesuai dokumen asli.',
        ])->assertSessionDoesntHaveErrors();

        $document->refresh();
        $this->assertSame('REJECTED', $document->verification_status);
        $this->assertSame($verifier->id, $document->verified_by);
        $this->assertNotNull($document->verified_at);
    }

    public function test_resubmit_clears_review_fields_and_invalid_repeat_has_no_extra_audit(): void
    {
        $document = $this->document(['verification_status' => 'REJECTED']);
        $verifier = User::factory()->create();
        $verifier->givePermissionTo('employee-documents.verify');
        $document->update([
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_reason' => 'Perlu koreksi.',
        ]);

        $this->actingAs($verifier)->post(route('hr.employee-documents.resubmit', $document))->assertSessionDoesntHaveErrors();
        $document->refresh();
        $this->assertSame('PENDING', $document->verification_status);
        $this->assertNull($document->verified_by);
        $this->assertNull($document->verified_at);
        $this->assertNull($document->verification_reason);

        $auditCount = AuditLog::query()->where('event', 'EmployeeDocument.resubmitted')->count();
        $this->actingAs($verifier)->post(route('hr.employee-documents.resubmit', $document))->assertSessionHasErrors('status');
        $this->assertSame($auditCount, AuditLog::query()->where('event', 'EmployeeDocument.resubmitted')->count());
        $this->assertSame('PENDING', $document->refresh()->verification_status);
    }

    public function test_user_without_verify_permission_cannot_call_any_verification_mutation(): void
    {
        $document = $this->document();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('hr.employee-documents.verify', $document))->assertForbidden();
        $this->actingAs($user)->post(route('hr.employee-documents.reject', $document), ['reason' => 'Unauthorized'])->assertForbidden();
        $this->actingAs($user)->post(route('hr.employee-documents.resubmit', $document))->assertForbidden();
        $this->assertSame('PENDING', $document->refresh()->verification_status);
    }

    public function test_material_metadata_change_resets_verification_in_the_same_update(): void
    {
        $verifier = User::factory()->create();
        $document = $this->document([
            'verification_status' => 'VERIFIED',
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_reason' => 'Valid.',
        ]);

        $document->update(['issuer' => 'Penerbit baru']);

        $document->refresh();
        $this->assertSame('PENDING', $document->verification_status);
        $this->assertNull($document->verified_by);
        $this->assertNull($document->verified_at);
        $this->assertNull($document->verification_reason);
    }

    private function document(array $overrides = []): EmployeeDocument
    {
        $status = EmploymentStatus::query()->firstOrCreate(['code' => 'ACTIVE'], ['name' => 'Active', 'active' => true]);
        $employmentType = EmploymentType::query()->firstOrCreate(['code' => 'PERM'], ['name' => 'Permanent', 'active' => true]);
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-VERIFY-'.Employee::query()->count(), 'first_name' => 'Verify', 'display_name' => 'Verify Employee',
            'employment_status_id' => $status->id, 'employment_type_id' => $employmentType->id, 'active' => true,
        ]);
        $documentType = ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'KTP')->firstOrFail();

        return EmployeeDocument::query()->create([...[
            'employee_id' => $employee->id,
            'document_type_id' => $documentType->id,
            'verification_status' => 'PENDING',
        ], ...$overrides]);
    }
}
