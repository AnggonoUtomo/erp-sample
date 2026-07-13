<?php

namespace Tests\Feature;

use App\Modules\DocumentManagement\Foundation\DTO\CreateLogicalDocumentV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Services\LogicalDocumentService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class DocumentManagementLogicalDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_persists_only_logical_metadata_and_returns_safe_contract(): void
    {
        $result = app(LogicalDocumentService::class)->create(new CreateLogicalDocumentV1(
            ownerContext: new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            idempotencyKey: 'request-employee-document-817',
            actorReference: 'user:41',
        ));

        $this->assertMatchesRegularExpression('/^dms_[0-9A-HJKMNP-TV-Z]{26}$/', $result->reference->value());
        $this->assertSame([
            'schemaVersion' => 1,
            'reference' => $result->reference->value(),
            'status' => 'PENDING',
        ], $result->toArray());
        $this->assertEmpty(array_intersect(
            ['path', 'url', 'disk', 'binary', 'objectKey', 'storageKey'],
            array_keys($result->toArray()),
        ));
        $this->assertDatabaseHas('dm_documents', [
            'reference' => $result->reference->value(),
            'owner_schema_version' => 1,
            'owner_domain' => 'HR',
            'owner_aggregate_type' => 'EmployeeDocument',
            'owner_aggregate_id' => '817',
            'status' => 'PENDING',
            'created_by_reference' => 'user:41',
        ]);
        $this->assertTrue(Schema::hasColumns('dm_documents', ['current_version_id']));
        $this->assertTrue(Schema::hasColumns('dm_idempotency_keys', ['expires_at']));
    }

    public function test_identical_retry_returns_same_document_without_storing_raw_key(): void
    {
        $request = new CreateLogicalDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            'idempotency-key-that-must-not-be-stored',
            'user:41',
        );

        $first = app(LogicalDocumentService::class)->create($request);
        $retry = app(LogicalDocumentService::class)->create($request);

        $this->assertSame($first->toArray(), $retry->toArray());
        $this->assertDatabaseCount('dm_documents', 1);
        $this->assertDatabaseCount('dm_idempotency_keys', 1);
        $this->assertFalse(DB::table('dm_idempotency_keys')->where(
            'key_hash',
            'idempotency-key-that-must-not-be-stored',
        )->exists());
    }

    public function test_same_idempotency_key_with_different_owner_is_rejected_without_partial_record(): void
    {
        $service = app(LogicalDocumentService::class);
        $service->create(new CreateLogicalDocumentV1(
            new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'),
            'same-request-key',
            'user:41',
        ));

        $this->expectException(DomainException::class);

        try {
            $service->create(new CreateLogicalDocumentV1(
                new DocumentOwnerContextV1('HR', 'EmployeeDocument', '818'),
                'same-request-key',
                'user:41',
            ));
        } finally {
            $this->assertDatabaseCount('dm_documents', 1);
            $this->assertDatabaseCount('dm_idempotency_keys', 1);
        }
    }

    public function test_reference_is_unique_immutable_and_owner_context_is_indexed(): void
    {
        $document = LogicalDocument::query()->create([
            'reference' => 'dms_01J00000000000000000000000',
            'owner_schema_version' => 1,
            'owner_domain' => 'HR',
            'owner_aggregate_type' => 'EmployeeDocument',
            'owner_aggregate_id' => '817',
            'status' => 'PENDING',
            'created_by_reference' => 'user:41',
        ]);

        $this->assertContains(
            ['owner_domain', 'owner_aggregate_type', 'owner_aggregate_id'],
            collect(Schema::getIndexes('dm_documents'))->pluck('columns')->all(),
        );
        $document->reference = 'dms_01J00000000000000000000001';

        $this->expectException(LogicException::class);
        $document->save();
    }

    public function test_create_request_rejects_unbounded_or_blank_sensitive_inputs(): void
    {
        foreach ([
            fn () => new CreateLogicalDocumentV1(new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'), '', 'user:41'),
            fn () => new CreateLogicalDocumentV1(new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'), str_repeat('x', 256), 'user:41'),
            fn () => new CreateLogicalDocumentV1(new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817'), 'valid-key', ''),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Invalid logical document request was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
