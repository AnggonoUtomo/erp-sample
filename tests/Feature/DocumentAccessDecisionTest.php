<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\DocumentManagement\Foundation\Access\DTO\DocumentAccessRequestV1;
use App\Modules\DocumentManagement\Foundation\Access\Services\DocumentAccessDecisionService;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentAccessGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Lifecycle\Services\DocumentLifecycleService;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentAccessDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_dms_permission_owner_and_available_state_grant_access_without_storage_details(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->userWithPermission('documents.view');

        $gateway = app(DocumentAccessGateway::class);
        $this->assertInstanceOf(DocumentAccessDecisionService::class, $gateway);
        $decision = $gateway->decide(
            $this->request($document->reference, "user:{$user->id}", 'VIEW'),
        );

        $this->assertSame('AVAILABLE', $decision->state);
        $this->assertSame([
            'schemaVersion' => 1,
            'reference' => $document->reference,
            'action' => 'VIEW',
            'state' => 'AVAILABLE',
        ], $decision->toArray());
        $this->assertSame(['schemaVersion', 'reference', 'action', 'state'], array_keys($decision->toArray()));
    }

    public function test_guest_consumer_permission_idor_and_unknown_action_are_denied_before_state_disclosure(): void
    {
        [$document] = $this->availableDocument();
        Permission::findOrCreate('employee-documents.view');
        $consumerOnly = User::factory()->create();
        $consumerOnly->givePermissionTo('employee-documents.view');
        $dmsViewer = $this->userWithPermission('documents.view');

        $cases = [
            $this->request($document->reference, null, 'VIEW'),
            $this->request($document->reference, 'guest', 'VIEW'),
            $this->request($document->reference, "user:{$consumerOnly->id}", 'VIEW'),
            $this->request(
                $document->reference,
                "user:{$dmsViewer->id}",
                'VIEW',
                new DocumentOwnerContextV1('HR', 'EmployeeDocument', 'different-owner'),
            ),
            $this->request($document->reference, "user:{$dmsViewer->id}", 'DELETE'),
        ];

        foreach ($cases as $request) {
            $this->assertSame('DENIED', app(DocumentAccessDecisionService::class)->decide($request)->state);
        }
    }

    public function test_action_permissions_are_independent_and_revocation_fails_closed(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->userWithPermission('documents.view');
        Permission::findOrCreate('documents.download');
        $download = $this->request($document->reference, "user:{$user->id}", 'DOWNLOAD');

        $this->assertSame('DENIED', app(DocumentAccessDecisionService::class)->decide($download)->state);
        $user->givePermissionTo('documents.download');
        $this->assertSame('AVAILABLE', app(DocumentAccessDecisionService::class)->decide($download)->state);
        $user->revokePermissionTo('documents.download');
        $this->assertSame('DENIED', app(DocumentAccessDecisionService::class)->decide($download)->state);
        $this->assertSame(
            'AVAILABLE',
            app(DocumentAccessDecisionService::class)
                ->decide($this->request($document->reference, "user:{$user->id}", 'VIEW'))->state,
        );
    }

    public function test_authorized_missing_archived_and_unavailable_states_are_deterministic(): void
    {
        [$document, $storage] = $this->availableDocument();
        $user = $this->userWithPermission('documents.view');
        $actor = "user:{$user->id}";
        $service = app(DocumentAccessDecisionService::class);

        $this->assertSame('MISSING', $service->decide($this->request('dms_missing', $actor, 'VIEW'))->state);

        app(DocumentLifecycleService::class)->archive(
            new DocumentReferenceV1($document->reference),
            'user:41',
            'Retention review',
        );
        $this->assertSame('ARCHIVED', $service->decide($this->request($document->reference, $actor, 'VIEW'))->state);

        app(DocumentLifecycleService::class)->restore(new DocumentReferenceV1($document->reference), 'user:41');
        $version = DocumentVersion::query()->findOrFail($document->current_version_id);
        $storage->deleteFailedObject(new StorageObjectKeyV1($version->storage_object_key));
        $this->assertSame('UNAVAILABLE', $service->decide($this->request($document->reference, $actor, 'VIEW'))->state);
    }

    public function test_grants_and_denials_are_audited_without_owner_or_storage_secrets(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->userWithPermission('documents.view');
        $sessionUser = User::factory()->create();
        $this->actingAs($sessionUser);
        $service = app(DocumentAccessDecisionService::class);

        $service->decide($this->request($document->reference, null, 'VIEW'));
        $service->decide($this->request($document->reference, "user:{$user->id}", 'VIEW'));

        $this->assertDatabaseHas('audit_logs', ['event' => 'DocumentAccess.denied']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'DocumentAccess.granted']);
        $this->assertNull(AuditLog::query()->where('event', 'DocumentAccess.denied')->latest('id')->value('actor_id'));
        $this->assertSame(
            $user->id,
            AuditLog::query()->where('event', 'DocumentAccess.granted')->latest('id')->value('actor_id'),
        );
        foreach (AuditLog::query()->whereIn('event', ['DocumentAccess.denied', 'DocumentAccess.granted'])->get() as $audit) {
            $values = $audit->new_values ?? [];
            $this->assertSame([], array_intersect(
                ['storage_object_key', 'path', 'url', 'owner_aggregate_id'],
                array_keys($values),
            ));
        }
    }

    public function test_manifest_and_schema_publish_access_gateway_v1(): void
    {
        $module = require base_path('app/Modules/DocumentManagement/Foundation/module.php');
        $contract = collect($module['integrations']['contracts'])
            ->firstWhere('name', 'DocumentAccessGateway');
        $schemaPath = base_path('app/Modules/DocumentManagement/Foundation/'.$contract['schema']);
        $schema = json_decode((string) file_get_contents($schemaPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(1, $contract['schema_version']);
        $this->assertSame(DocumentAccessGateway::class, $contract['reader']);
        $this->assertSame(DocumentReferenceDescriptorV1::STATES, $contract['states']);
        $this->assertSame(DocumentReferenceDescriptorV1::STATES, $schema['properties']['state']['enum']);
        $this->assertSame(['schemaVersion', 'reference', 'action', 'state'], $schema['required']);
    }

    private function request(
        string $reference,
        ?string $actorReference,
        string $action,
        ?DocumentOwnerContextV1 $owner = null,
    ): DocumentAccessRequestV1 {
        return new DocumentAccessRequestV1(
            new DocumentReferenceV1($reference),
            $actorReference,
            $action,
            $owner ?? new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
        );
    }

    /** @return array{LogicalDocument, InMemoryStorageAdapter} */
    private function availableDocument(): array
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";
        $result = app(DocumentIngestionService::class)->ingest(new IngestDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), 'access-817'),
            'user:41',
            $this->stream($contents),
        ));

        return [
            LogicalDocument::query()->where('reference', $result->reference->value())->firstOrFail(),
            $storage,
        ];
    }

    private function userWithPermission(string $permission): User
    {
        Permission::findOrCreate($permission);
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        return $user;
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
