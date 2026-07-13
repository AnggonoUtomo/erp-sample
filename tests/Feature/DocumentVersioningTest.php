<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Exceptions\DocumentIngestionDisabled;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use App\Modules\DocumentManagement\Foundation\Versioning\DTO\ReplaceDocumentVersionV1;
use App\Modules\DocumentManagement\Foundation\Versioning\Services\DocumentVersioningService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use LogicException;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacement_adds_immutable_version_and_switches_current_atomically(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $oldVersion = DocumentVersion::query()->findOrFail($document->current_version_id);
        $oldKey = $oldVersion->storage_object_key;

        $result = app(DocumentVersioningService::class)->replace($this->replacement(
            $document->reference,
            "%PDF-1.7\nnew\n%%EOF\n",
        ));

        $document->refresh();
        $newVersion = DocumentVersion::query()->findOrFail($document->current_version_id);
        $this->assertSame(2, $result->version);
        $this->assertSame('AVAILABLE', $result->status);
        $this->assertSame(2, $newVersion->version_number);
        $this->assertNotSame($oldVersion->getKey(), $newVersion->getKey());
        $this->assertSame($oldKey, $oldVersion->fresh()->storage_object_key);
        $this->assertTrue($storage->exists(new StorageObjectKeyV1($oldKey)));
        $oldStream = $storage->read(new StorageObjectKeyV1($oldKey));
        try {
            $this->assertSame("%PDF-1.7\nold\n%%EOF\n", stream_get_contents($oldStream));
        } finally {
            fclose($oldStream);
        }
        $this->assertDatabaseCount('dm_document_versions', 2);
        $this->assertSame(2, $storage->objectCount());
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'document-management.foundation',
            'event' => 'DocumentVersion.replaced',
        ]);
    }

    public function test_identical_retry_is_reused_and_changed_fingerprint_conflicts(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $request = $this->replacement($document->reference, "%PDF-1.7\nnew\n%%EOF\n");

        $first = app(DocumentVersioningService::class)->replace($request);
        $retry = app(DocumentVersioningService::class)->replace($request);

        $this->assertSame($first->toArray(), $retry->toArray());
        $this->assertDatabaseCount('dm_document_versions', 2);
        $this->assertSame(2, $storage->objectCount());

        $this->expectException(DomainException::class);
        app(DocumentVersioningService::class)->replace(
            $this->replacement($document->reference, "%PDF-1.7\nchanged\n%%EOF\n"),
        );
    }

    public function test_promotion_failure_preserves_old_current_version_and_binary(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $oldCurrentVersionId = $document->current_version_id;

        foreach (['promote', 'promote_after_move'] as $failure) {
            $storage->failOn($failure);

            try {
                app(DocumentVersioningService::class)->replace(
                    $this->replacement($document->reference, "%PDF-1.7\nnew\n%%EOF\n"),
                );
                $this->fail("{$failure} failure was ignored.");
            } catch (RuntimeException) {
                $this->addToAssertionCount(1);
            }

            $this->assertSame($oldCurrentVersionId, $document->fresh()->current_version_id);
            $this->assertDatabaseCount('dm_document_versions', 1);
            $this->assertSame(1, $storage->objectCount());
        }
    }

    public function test_available_version_integrity_metadata_cannot_be_mutated(): void
    {
        $storage = new InMemoryStorageAdapter;
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $version = DocumentVersion::query()->findOrFail($document->current_version_id);

        $this->expectException(LogicException::class);
        $version->update(['sha256' => str_repeat('0', 64)]);
    }

    public function test_disabled_ingestion_gate_preserves_current_version_without_new_object(): void
    {
        $storage = new InMemoryStorageAdapter;
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $currentVersionId = $document->current_version_id;
        config(['document-management.ingestion_enabled' => false]);

        try {
            app(DocumentVersioningService::class)->replace(
                $this->replacement($document->reference, "%PDF-1.7\nnew\n%%EOF\n"),
            );
            $this->fail('Disabled replacement gate was ignored.');
        } catch (DocumentIngestionDisabled) {
            $this->addToAssertionCount(1);
        }

        $this->assertSame($currentVersionId, $document->fresh()->current_version_id);
        $this->assertDatabaseCount('dm_document_versions', 1);
        $this->assertSame(1, $storage->objectCount());
    }

    public function test_replacement_route_requires_authentication_permission_and_existing_reference(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $document = $this->ingest($storage, "%PDF-1.7\nold\n%%EOF\n");
        $route = "/document-management/documents/{$document->reference}/versions";
        $payload = $this->httpPayload();

        $this->post($route, $payload)->assertRedirect();
        $user = User::factory()->create();
        $this->actingAs($user)->post($route, $payload)->assertForbidden();

        Permission::findOrCreate('documents.replace');
        $user->givePermissionTo('documents.replace');
        $this->actingAs($user)->postJson($route, $this->httpPayload())
            ->assertCreated()
            ->assertJsonPath('data.version', 2)
            ->assertJsonMissingPath('data.storageKey');
        $this->actingAs($user)->postJson(
            '/document-management/documents/dms_missing/versions',
            $this->httpPayload('http-replace-missing'),
        )
            ->assertNotFound();
    }

    private function ingest(InMemoryStorageAdapter $storage, string $contents): LogicalDocument
    {
        $this->app->instance(StorageAdapter::class, $storage);
        $result = app(DocumentIngestionService::class)->ingest(new IngestDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), 'initial-817'),
            'user:41',
            $this->stream($contents),
        ));

        return LogicalDocument::query()->where('reference', $result->reference->value())->firstOrFail();
    }

    private function replacement(string $reference, string $contents): ReplaceDocumentVersionV1
    {
        return new ReplaceDocumentVersionV1(
            $reference,
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), 'replace-817'),
            'user:41',
            $this->stream($contents),
        );
    }

    /** @return array<string, mixed> */
    private function httpPayload(string $idempotencyKey = 'http-replace-817'): array
    {
        return [
            'idempotency_key' => $idempotencyKey,
            'file' => UploadedFile::fake()->createWithContent('contract.pdf', "%PDF-1.7\nnew\n%%EOF\n"),
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
