<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DocumentManagement\Foundation\Models\DeliveryToken;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
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

class EmployeeDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(HRReferenceDataSeeder::class);
        foreach (['employee-documents.attach', 'employee-documents.view', 'documents.download'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $this->app->instance(StorageAdapter::class, new InMemoryStorageAdapter);
    }

    public function test_user_with_hr_and_dms_permissions_receives_safe_handoff_and_downloads_through_dms(): void
    {
        [$document, $user, $contents] = $this->attachedDocument('ACCESS-001');

        $response = $this->actingAs($user)->postJson(route('hr.employee-documents.attachment.delivery', $document))
            ->assertCreated()
            ->assertJsonPath('data.action', 'DOWNLOAD')
            ->assertJsonMissingPath('data.url')
            ->assertJsonMissingPath('data.path')
            ->assertJsonMissingPath('data.storageKey');
        $token = $response->json('data.token');

        $download = $this->post('/document-management/deliveries/consume', ['token' => $token]);
        $download->assertOk()->assertDownload('employee-document.pdf');
        $this->assertSame($contents, $download->streamedContent());
    }

    public function test_hr_and_dms_permissions_are_both_required_and_missing_reference_fails_closed(): void
    {
        [$document, $authorized] = $this->attachedDocument('ACCESS-002');
        $dmsOnly = User::factory()->create();
        $dmsOnly->givePermissionTo('documents.download');
        $hrOnly = User::factory()->create();
        $hrOnly->givePermissionTo('employee-documents.view');

        $this->actingAs($dmsOnly)->postJson(route('hr.employee-documents.attachment.delivery', $document))->assertForbidden();
        $this->actingAs($hrOnly)->postJson(route('hr.employee-documents.attachment.delivery', $document))->assertForbidden();

        $missing = $this->document('ACCESS-EMPTY');
        $this->actingAs($authorized)->postJson(route('hr.employee-documents.attachment.delivery', $missing))
            ->assertForbidden()->assertJsonPath('error.code', 'ATTACHMENT_ACCESS_DENIED');
    }

    public function test_owner_mismatch_idor_expired_replay_and_revoked_access_are_denied(): void
    {
        [$document, $user] = $this->attachedDocument('ACCESS-003');
        $idor = $this->document('ACCESS-IDOR');
        $idor->update([
            'document_reference' => $document->document_reference,
            'document_reference_version' => 1,
        ]);
        $this->actingAs($user)->postJson(route('hr.employee-documents.attachment.delivery', $idor))->assertForbidden();

        $oneTime = $this->postJson(route('hr.employee-documents.attachment.delivery', $document))->assertCreated()->json('data.token');
        $this->post('/document-management/deliveries/consume', ['token' => $oneTime])->assertOk();
        $this->postJson('/document-management/deliveries/consume', ['token' => $oneTime])->assertForbidden();

        $expired = $this->postJson(route('hr.employee-documents.attachment.delivery', $document))->assertCreated()->json('data.token');
        DeliveryToken::query()->where('token_hash', hash_hmac('sha256', $expired, config('app.key')))
            ->update(['expires_at' => now()->subSecond()]);
        $this->postJson('/document-management/deliveries/consume', ['token' => $expired])->assertForbidden();

        $revoked = $this->postJson(route('hr.employee-documents.attachment.delivery', $document))->assertCreated()->json('data.token');
        $user->revokePermissionTo('documents.download');
        $this->postJson('/document-management/deliveries/consume', ['token' => $revoked])->assertForbidden();
    }

    /** @return array{EmployeeDocument, User, string} */
    private function attachedDocument(string $number): array
    {
        $document = $this->document($number);
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-documents.attach', 'employee-documents.view', 'documents.download']);
        $contents = "%PDF-1.7\nemployee attachment\n%%EOF\n";
        $this->actingAs($user)->post(route('hr.employee-documents.attachment.store', $document), [
            'attachment' => UploadedFile::fake()->createWithContent('employee-document.pdf', $contents),
            'idempotency_key' => "attach-{$number}",
        ])->assertSessionHasNoErrors();

        return [$document->refresh(), $user, $contents];
    }

    private function document(string $number): EmployeeDocument
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
            'verification_status' => 'PENDING',
        ]);
    }
}
