<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Exceptions\DocumentIngestionDisabled;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_stream_publishes_one_available_version_with_integrity_metadata(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";

        $result = app(DocumentIngestionService::class)->ingest($this->request($contents));

        $this->assertSame([
            'schemaVersion' => 1,
            'reference' => $result->reference->value(),
            'version' => 1,
            'status' => 'AVAILABLE',
            'scanStatus' => 'NOT_CONFIGURED',
        ], $result->toArray());
        $this->assertDatabaseHas('dm_documents', [
            'reference' => $result->reference->value(),
            'status' => 'AVAILABLE',
        ]);
        $currentVersionId = (int) \DB::table('dm_documents')
            ->where('reference', $result->reference->value())
            ->value('current_version_id');
        $this->assertGreaterThan(0, $currentVersionId);
        $this->assertDatabaseHas('dm_document_versions', [
            'version_number' => 1,
            'original_filename' => 'contract.pdf',
            'declared_media_type' => 'application/pdf',
            'detected_media_type' => 'application/pdf',
            'byte_size' => strlen($contents),
            'sha256' => hash('sha256', $contents),
            'status' => 'AVAILABLE',
            'scan_status' => 'NOT_CONFIGURED',
        ]);
        $this->assertSame(1, $storage->objectCount());
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'document-management.foundation',
            'event' => 'DocumentVersion.available',
        ]);
        $auditValues = AuditLog::query()->where('event', 'DocumentVersion.available')->firstOrFail()->new_values;
        $this->assertArrayNotHasKey('storage_object_key', $auditValues);
        $this->assertArrayNotHasKey('original_filename', $auditValues);
        $this->assertArrayNotHasKey('sha256', $auditValues);
    }

    public function test_identical_retry_returns_same_result_without_second_object_or_rows(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";

        $first = app(DocumentIngestionService::class)->ingest($this->request($contents));
        $retry = app(DocumentIngestionService::class)->ingest($this->request($contents));

        $this->assertSame($first->toArray(), $retry->toArray());
        $this->assertDatabaseCount('dm_documents', 1);
        $this->assertDatabaseCount('dm_document_versions', 1);
        $this->assertDatabaseCount('dm_idempotency_keys', 1);
        $this->assertSame(1, $storage->objectCount());
    }

    public function test_same_key_with_different_content_is_conflict_without_partial_state(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        app(DocumentIngestionService::class)->ingest($this->request("%PDF-1.7\nfirst\n%%EOF\n"));

        $this->expectException(DomainException::class);

        try {
            app(DocumentIngestionService::class)->ingest($this->request("%PDF-1.7\nsecond\n%%EOF\n"));
        } finally {
            $this->assertDatabaseCount('dm_documents', 1);
            $this->assertDatabaseCount('dm_document_versions', 1);
            $this->assertSame(1, $storage->objectCount());
        }
    }

    public function test_stage_and_promote_failures_leave_no_database_or_storage_partial(): void
    {
        foreach (['stage', 'promote'] as $failure) {
            $storage = new InMemoryStorageAdapter(failOperation: $failure);
            $this->app->instance(StorageAdapter::class, $storage);

            try {
                app(DocumentIngestionService::class)->ingest($this->request("%PDF-1.7\nbody\n%%EOF\n"));
                $this->fail("{$failure} failure was ignored.");
            } catch (RuntimeException) {
                $this->addToAssertionCount(1);
            }

            $this->assertDatabaseCount('dm_documents', 0);
            $this->assertDatabaseCount('dm_document_versions', 0);
            $this->assertDatabaseCount('dm_idempotency_keys', 0);
            $this->assertSame(0, $storage->objectCount());
        }
    }

    public function test_ingestion_route_requires_authentication_and_upload_permission(): void
    {
        $payload = [
            'owner_domain' => 'HR',
            'owner_aggregate_type' => 'EmployeeDocument',
            'owner_aggregate_id' => '817',
            'idempotency_key' => 'http-upload-817',
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.7\nbody\n%%EOF\n"),
        ];

        $this->post('/document-management/documents', $payload)->assertRedirect();

        $user = User::factory()->create();
        $this->actingAs($user)->post('/document-management/documents', $payload)->assertForbidden();

        Permission::findOrCreate('documents.upload');
        $user->givePermissionTo('documents.upload');
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);

        $this->actingAs($user)
            ->postJson('/document-management/documents', $payload)
            ->assertCreated()
            ->assertJsonPath('data.status', 'AVAILABLE')
            ->assertJsonPath('data.scanStatus', 'NOT_CONFIGURED')
            ->assertJsonMissingPath('data.path')
            ->assertJsonMissingPath('data.storageKey');
    }

    public function test_route_returns_validation_error_for_spoofed_file_and_conflict_for_reused_key(): void
    {
        Permission::findOrCreate('documents.upload');
        $user = User::factory()->create();
        $user->givePermissionTo('documents.upload');
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);

        $base = [
            'owner_domain' => 'HR',
            'owner_aggregate_type' => 'EmployeeDocument',
            'owner_aggregate_id' => '817',
            'idempotency_key' => 'http-conflict-817',
        ];
        $this->actingAs($user)->postJson('/document-management/documents', $base + [
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', 'not-a-pdf'),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');

        $this->actingAs($user)->postJson('/document-management/documents', $base + [
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.7\nfirst\n%%EOF\n"),
        ])->assertCreated();
        $this->actingAs($user)->postJson('/document-management/documents', $base + [
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.7\nsecond\n%%EOF\n"),
        ])->assertConflict()->assertJsonPath('error.code', 'IDEMPOTENCY_CONFLICT');
    }

    public function test_disabled_ingestion_gate_fails_closed_without_database_or_storage_write(): void
    {
        config(['document-management.ingestion_enabled' => false]);
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";

        try {
            app(DocumentIngestionService::class)->ingest($this->request($contents));
            $this->fail('Disabled ingestion gate was ignored.');
        } catch (DocumentIngestionDisabled) {
            $this->addToAssertionCount(1);
        }

        $this->assertDatabaseCount('dm_documents', 0);
        $this->assertDatabaseCount('dm_document_versions', 0);
        $this->assertSame(0, $storage->objectCount());

        Permission::findOrCreate('documents.upload');
        $user = User::factory()->create();
        $user->givePermissionTo('documents.upload');
        $this->actingAs($user)->postJson('/document-management/documents', [
            'owner_domain' => 'HR',
            'owner_aggregate_type' => 'EmployeeDocument',
            'owner_aggregate_id' => '817',
            'idempotency_key' => 'disabled-817',
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', $contents),
        ])->assertServiceUnavailable()->assertJsonPath('error.code', 'DMS_INGESTION_DISABLED');
    }

    private function request(string $contents): IngestDocumentV1
    {
        return new IngestDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), 'ingest-817'),
            'user:41',
            $this->stream($contents),
        );
    }

    /** @return resource */
    private function stream(string $contents)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);

        return $stream;
    }
}
