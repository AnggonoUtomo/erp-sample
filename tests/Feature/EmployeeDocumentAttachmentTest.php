<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\HR\EmployeeDocuments\Integration\Contracts\EmployeeDocumentAttachmentGateway;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAttachmentUnavailable;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class EmployeeDocumentAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HRReferenceDataSeeder::class);
        Permission::findOrCreate('employee-documents.attach');
        $this->app->instance(StorageAdapter::class, new InMemoryStorageAdapter);
    }

    public function test_authorized_attach_persists_opaque_reference_only_after_available_and_is_idempotent(): void
    {
        $document = $this->document('ATTACH-001', 'VERIFIED');
        $user = $this->user();
        $payload = $this->payload('attach-employee-document-001');

        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $payload)
            ->assertRedirect()->assertSessionHasNoErrors();
        $reference = $document->refresh()->document_reference;
        $this->assertNotNull($reference);
        $this->assertSame(1, $document->document_reference_version);
        $this->assertSame('PENDING', $document->verification_status);
        $this->assertDatabaseCount('dm_documents', 1);
        $this->assertDatabaseCount('dm_document_versions', 1);

        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('attach-employee-document-001'))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($reference, $document->refresh()->document_reference);
        $this->assertDatabaseCount('dm_documents', 1);
        $audit = AuditLog::query()->where('event', 'EmployeeDocument.attachment_attached')->firstOrFail();
        $this->assertArrayNotHasKey('reference', $audit->new_values);
        $this->assertStringNotContainsString('storage', json_encode($audit->toArray(), JSON_THROW_ON_ERROR));
    }

    public function test_timeout_after_dms_success_retries_without_duplicate_or_partial_hr_reference(): void
    {
        $document = $this->document('ATTACH-002');
        $user = $this->user();
        $real = app(EmployeeDocumentAttachmentGateway::class);
        $this->app->instance(EmployeeDocumentAttachmentGateway::class, new class($real) implements EmployeeDocumentAttachmentGateway
        {
            private bool $first = true;

            public function __construct(private EmployeeDocumentAttachmentGateway $real) {}

            public function createFor(AttachEmployeeDocumentV1 $request): DocumentReferenceV1
            {
                $reference = $this->real->createFor($request);
                if ($this->first) {
                    $this->first = false;
                    throw new EmployeeDocumentAttachmentUnavailable('simulated response timeout');
                }

                return $reference;
            }
        });

        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('attach-timeout-002'))
            ->assertSessionHasErrors('attachment');
        $this->assertNull($document->refresh()->document_reference);
        $this->assertNotNull($document->attachment_idempotency_key_hash);
        $this->assertDatabaseCount('dm_documents', 1);

        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('attach-timeout-002'))
            ->assertSessionHasNoErrors();
        $this->assertNotNull($document->refresh()->document_reference);
        $this->assertNotNull($document->attachment_idempotency_key_hash);
        $this->assertDatabaseCount('dm_documents', 1);
    }

    public function test_different_key_is_rejected_while_pending_and_unauthorized_actor_cannot_attach_or_detach(): void
    {
        $document = $this->document('ATTACH-003');
        $document->update(['attachment_idempotency_key_hash' => hash_hmac('sha256', 'original-key', config('app.key'))]);
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('other-key'))
            ->assertForbidden();
        $this->actingAs($user)->delete(route('hr.employee-documents.attachment.destroy', $document))->assertForbidden();
        $this->assertNull($document->refresh()->document_reference);

        $user->givePermissionTo('employee-documents.attach');
        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('other-key'))
            ->assertSessionHasErrors('idempotency_key');
        $this->assertDatabaseCount('dm_documents', 0);
    }

    public function test_detach_clears_only_hr_reference_resets_verification_and_preserves_dms_binary(): void
    {
        $document = $this->document('ATTACH-004', 'VERIFIED');
        $user = $this->user();
        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), $this->payload('attach-detach-004'));
        $reference = $document->refresh()->document_reference;

        $this->actingAs($user)->delete(route('hr.employee-documents.attachment.destroy', $document))
            ->assertRedirect()->assertSessionHasNoErrors();

        $document->refresh();
        $this->assertNull($document->document_reference);
        $this->assertNull($document->document_reference_version);
        $this->assertSame('PENDING', $document->verification_status);
        $this->assertDatabaseHas('dm_documents', ['reference' => $reference, 'status' => 'AVAILABLE']);
        $this->assertDatabaseCount('dm_document_versions', 1);
        $audit = AuditLog::query()->where('event', 'EmployeeDocument.attachment_detached')->firstOrFail();
        $this->assertArrayNotHasKey('reference', $audit->old_values);
    }

    private function user(): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.attach');

        return $user;
    }

    private function payload(string $key): array
    {
        $contents = "%PDF-1.7\nattachment\n%%EOF\n";

        return [
            'attachment' => UploadedFile::fake()->createWithContent('attachment.pdf', $contents),
            'idempotency_key' => $key,
        ];
    }

    private function document(string $number, string $verification = 'PENDING'): EmployeeDocument
    {
        $status = EmploymentStatus::query()->firstOrCreate(['code' => 'ACTIVE'], ['name' => 'Active', 'active' => true]);
        $type = EmploymentType::query()->firstOrCreate(['code' => 'PERM'], ['name' => 'Permanent', 'active' => true]);
        $employee = Employee::query()->create([
            'employee_number' => $number, 'first_name' => $number, 'display_name' => $number,
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);

        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'KTP')->value('id'),
            'verification_status' => $verification,
        ]);
    }
}
