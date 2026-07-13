<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Lifecycle\Services\DocumentLifecycleService;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_soft_deletes_metadata_without_deleting_binary_and_is_idempotent(): void
    {
        [$document, $storage] = $this->availableDocument();
        $version = DocumentVersion::query()->findOrFail($document->current_version_id);

        $first = app(DocumentLifecycleService::class)->archive(
            new DocumentReferenceV1($document->reference),
            'user:41',
            'Retention review',
        );
        $retry = app(DocumentLifecycleService::class)->archive(
            new DocumentReferenceV1($document->reference),
            'user:41',
            'Retention review',
        );

        $this->assertSame('ARCHIVED', $first->state);
        $this->assertSame($first->toArray(), $retry->toArray());
        $this->assertSoftDeleted('dm_documents', ['id' => $document->getKey(), 'status' => 'ARCHIVED']);
        $this->assertTrue($storage->exists(new StorageObjectKeyV1($version->storage_object_key)));
        $this->assertSame(1, $storage->objectCount());
        $this->assertDatabaseCount('dm_document_versions', 1);
        $this->assertDatabaseCount('audit_logs', 2); // ingestion + one archive; retry is a no-op
        $this->assertDatabaseHas('audit_logs', ['event' => 'Document.archived']);
    }

    public function test_restore_revalidates_current_version_and_returns_available_descriptor(): void
    {
        [$document, $storage] = $this->availableDocument();
        $reference = new DocumentReferenceV1($document->reference);
        app(DocumentLifecycleService::class)->archive($reference, 'user:41', 'Mistaken archive');

        $descriptor = app(DocumentLifecycleService::class)->restore($reference, 'user:41');

        $this->assertSame('AVAILABLE', $descriptor->state);
        $this->assertNull(LogicalDocument::withTrashed()->findOrFail($document->getKey())->deleted_at);
        $this->assertSame('AVAILABLE', $document->fresh()->status);
        $this->assertSame(1, $storage->objectCount());
        $this->assertDatabaseHas('audit_logs', ['event' => 'Document.restored']);
    }

    public function test_restore_with_missing_current_binary_fails_and_remains_archived(): void
    {
        [$document, $storage] = $this->availableDocument();
        $reference = new DocumentReferenceV1($document->reference);
        $version = DocumentVersion::query()->findOrFail($document->current_version_id);
        app(DocumentLifecycleService::class)->archive($reference, 'user:41', 'Retention review');
        $storage->deleteFailedObject(new StorageObjectKeyV1($version->storage_object_key));

        $this->expectException(DomainException::class);

        try {
            app(DocumentLifecycleService::class)->restore($reference, 'user:41');
        } finally {
            $this->assertSoftDeleted('dm_documents', ['id' => $document->getKey(), 'status' => 'ARCHIVED']);
            $this->assertDatabaseMissing('audit_logs', ['event' => 'Document.restored']);
        }
    }

    public function test_restore_rejects_invalid_owner_context_and_remains_archived(): void
    {
        [$document] = $this->availableDocument();
        $reference = new DocumentReferenceV1($document->reference);
        app(DocumentLifecycleService::class)->archive($reference, 'user:41', 'Retention review');
        LogicalDocument::withTrashed()->whereKey($document->getKey())->update(['owner_aggregate_id' => '']);

        $this->expectException(DomainException::class);

        try {
            app(DocumentLifecycleService::class)->restore($reference, 'user:41');
        } finally {
            $this->assertSoftDeleted('dm_documents', ['id' => $document->getKey(), 'status' => 'ARCHIVED']);
        }
    }

    public function test_reader_returns_deterministic_safe_states_without_storage_details(): void
    {
        [$document, $storage] = $this->availableDocument();
        $reader = app(DocumentReferenceReader::class);
        $reference = new DocumentReferenceV1($document->reference);

        $available = $reader->describe($reference, 'user:41');
        $this->assertSame('AVAILABLE', $available->state);
        $this->assertSame(['schemaVersion', 'reference', 'state'], array_keys($available->toArray()));
        $this->assertSame('MISSING', $reader->describe(new DocumentReferenceV1('dms_missing'), 'user:41')->state);
        $this->assertSame('DENIED', $reader->describe($reference, '')->state);

        app(DocumentLifecycleService::class)->archive($reference, 'user:41', 'Retention review');
        $this->assertSame('ARCHIVED', $reader->describe($reference, 'user:41')->state);

        app(DocumentLifecycleService::class)->restore($reference, 'user:41');
        $version = DocumentVersion::query()->findOrFail($document->current_version_id);
        $storage->deleteFailedObject(new StorageObjectKeyV1($version->storage_object_key));
        $this->assertSame('UNAVAILABLE', $reader->describe($reference, 'user:41')->state);
    }

    public function test_lifecycle_routes_require_exact_permissions_and_expose_no_force_delete(): void
    {
        [$document] = $this->availableDocument();
        $archiveRoute = "/document-management/documents/{$document->reference}";
        $restoreRoute = "{$archiveRoute}/restore";

        $this->delete($archiveRoute, ['reason' => 'Retention review'])->assertRedirect();
        $user = User::factory()->create();
        $this->actingAs($user)->delete($archiveRoute, ['reason' => 'Retention review'])->assertForbidden();

        foreach (['documents.archive', 'documents.restore'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $user->givePermissionTo('documents.archive');
        $this->actingAs($user)->deleteJson($archiveRoute, ['reason' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->assertSame('AVAILABLE', $document->fresh()->status);
        $this->actingAs($user)->deleteJson($archiveRoute, ['reason' => 'Retention review'])
            ->assertOk()->assertJsonPath('data.state', 'ARCHIVED');
        $this->actingAs($user)->patchJson($restoreRoute)->assertForbidden();

        $user->givePermissionTo('documents.restore');
        $this->actingAs($user)->patchJson($restoreRoute)
            ->assertOk()->assertJsonPath('data.state', 'AVAILABLE');
        $this->actingAs($user)->deleteJson("{$archiveRoute}/force")->assertNotFound();
    }

    /** @return array{LogicalDocument, InMemoryStorageAdapter} */
    private function availableDocument(): array
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";
        $result = app(DocumentIngestionService::class)->ingest(new IngestDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), 'lifecycle-817'),
            'user:41',
            $this->stream($contents),
        ));

        return [
            LogicalDocument::query()->where('reference', $result->reference->value())->firstOrFail(),
            $storage,
        ];
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
