<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\DocumentManagement\Foundation\Delivery\DTO\IssueDocumentDeliveryV1;
use App\Modules\DocumentManagement\Foundation\Delivery\Exceptions\DocumentDeliveryDenied;
use App\Modules\DocumentManagement\Foundation\Delivery\Services\DocumentDeliveryService;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\Services\DocumentIngestionService;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentDeliveryGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DeliveryToken;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\DTO\UploadIntentV1;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DocumentDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_returns_short_lived_opaque_token_but_persists_only_hash_and_safe_scope(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->downloadUser();

        $gateway = app(DocumentDeliveryGateway::class);
        $this->assertInstanceOf(DocumentDeliveryService::class, $gateway);
        $handoff = $gateway->issue($this->issueRequest($document, $user));

        $this->assertSame(1, $handoff->schemaVersion);
        $this->assertSame($document->reference, $handoff->reference->value());
        $this->assertSame('DOWNLOAD', $handoff->action);
        $this->assertGreaterThan(now(), $handoff->expiresAt);
        $this->assertLessThanOrEqual(now()->addMinutes(5), $handoff->expiresAt);
        $this->assertDatabaseCount('dm_delivery_tokens', 1);
        $record = DeliveryToken::query()->firstOrFail();
        $this->assertNotSame($handoff->token, $record->token_hash);
        $this->assertStringNotContainsString($handoff->token, json_encode($record->toArray(), JSON_THROW_ON_ERROR));
        $this->assertDatabaseHas('audit_logs', ['event' => 'DocumentDelivery.issued']);
        $audit = AuditLog::query()->where('event', 'DocumentDelivery.issued')->firstOrFail();
        $this->assertStringNotContainsString($handoff->token, json_encode($audit->toArray(), JSON_THROW_ON_ERROR));
    }

    public function test_consume_returns_private_stream_once_and_replay_is_denied(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->downloadUser();
        $service = app(DocumentDeliveryService::class);
        $handoff = $service->issue($this->issueRequest($document, $user));

        $payload = $service->consume($handoff->token, "user:{$user->id}", 'DOWNLOAD');
        try {
            $this->assertSame("%PDF-1.7\nbody\n%%EOF\n", stream_get_contents($payload->stream));
        } finally {
            fclose($payload->stream);
        }
        $this->assertSame('contract.pdf', $payload->filename);
        $this->assertSame('application/pdf', $payload->mediaType);
        $this->assertNotNull(DeliveryToken::query()->firstOrFail()->used_at);

        $this->expectException(DocumentDeliveryDenied::class);
        $service->consume($handoff->token, "user:{$user->id}", 'DOWNLOAD');
    }

    public function test_expired_revoked_wrong_actor_and_wrong_action_tokens_are_denied_uniformly(): void
    {
        foreach (['expired', 'revoked', 'wrong_actor', 'wrong_action'] as $case) {
            [$document] = $this->availableDocument("delivery-{$case}");
            $user = $this->downloadUser();
            $service = app(DocumentDeliveryService::class);
            $handoff = $service->issue($this->issueRequest($document, $user));

            match ($case) {
                'expired' => DeliveryToken::query()->update(['expires_at' => now()->subSecond()]),
                'revoked' => $service->revoke($handoff->token, 'user:999'),
                default => null,
            };

            try {
                $service->consume(
                    $handoff->token,
                    $case === 'wrong_actor' ? 'user:999' : "user:{$user->id}",
                    $case === 'wrong_action' ? 'VIEW' : 'DOWNLOAD',
                );
                $this->fail("{$case} token was accepted.");
            } catch (DocumentDeliveryDenied) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_permission_revocation_and_current_version_change_invalidate_issued_token(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->downloadUser();
        $service = app(DocumentDeliveryService::class);
        $permissionToken = $service->issue($this->issueRequest($document, $user));
        $user->revokePermissionTo('documents.download');

        try {
            $service->consume($permissionToken->token, "user:{$user->id}", 'DOWNLOAD');
            $this->fail('Revoked permission was ignored.');
        } catch (DocumentDeliveryDenied) {
            $this->addToAssertionCount(1);
        }

        $user->givePermissionTo('documents.download');
        $versionToken = $service->issue($this->issueRequest($document, $user));
        LogicalDocument::query()->whereKey($document->getKey())->update(['current_version_id' => null]);

        $this->expectException(DocumentDeliveryDenied::class);
        $service->consume($versionToken->token, "user:{$user->id}", 'DOWNLOAD');
    }

    public function test_http_handoff_and_consume_require_auth_and_return_safe_attachment_headers(): void
    {
        [$document] = $this->availableDocument();
        $user = $this->downloadUser();
        $issueUrl = "/document-management/documents/{$document->reference}/delivery";
        $owner = ['owner_domain' => 'HR', 'owner_aggregate_type' => 'EmployeeDocument', 'owner_aggregate_id' => '817'];

        $this->postJson($issueUrl, $owner)->assertUnauthorized();
        $response = $this->actingAs($user)->postJson($issueUrl, $owner)
            ->assertCreated()
            ->assertJsonPath('data.action', 'DOWNLOAD')
            ->assertJsonMissingPath('data.storageKey')
            ->assertJsonMissingPath('data.url');
        $token = $response->json('data.token');

        $this->app['auth']->logout();
        $this->postJson('/document-management/deliveries/consume', ['token' => $token])->assertUnauthorized();
        $download = $this->actingAs($user)->post('/document-management/deliveries/consume', ['token' => $token]);
        $download->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('x-content-type-options', 'nosniff')
            ->assertDownload('contract.pdf');
        $this->assertSame("%PDF-1.7\nbody\n%%EOF\n", $download->streamedContent());
    }

    /** @return array{LogicalDocument, InMemoryStorageAdapter} */
    private function availableDocument(string $idempotencyKey = 'delivery-817'): array
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $contents = "%PDF-1.7\nbody\n%%EOF\n";
        $result = app(DocumentIngestionService::class)->ingest(new IngestDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            new UploadIntentV1('contract.pdf', 'application/pdf', strlen($contents), $idempotencyKey),
            'user:41',
            $this->stream($contents),
        ));

        return [LogicalDocument::query()->where('reference', $result->reference->value())->firstOrFail(), $storage];
    }

    private function issueRequest(LogicalDocument $document, User $user): IssueDocumentDeliveryV1
    {
        return new IssueDocumentDeliveryV1(
            new DocumentReferenceV1($document->reference),
            "user:{$user->id}",
            'DOWNLOAD',
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
        );
    }

    private function downloadUser(): User
    {
        Permission::findOrCreate('documents.download');
        $user = User::factory()->create();
        $user->givePermissionTo('documents.download');

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
