<?php

namespace Tests\Feature;

use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestDocumentV1;
use App\Modules\DocumentManagement\Foundation\Ingestion\DTO\IngestionResultV1;
use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentIngestionGateway;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1 as DmsDocumentReferenceV1;
use App\Modules\DocumentManagement\Foundation\Models\DocumentVersion;
use App\Modules\DocumentManagement\Foundation\Models\LogicalDocument;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use App\Modules\HR\EmployeeDocuments\Integration\Adapters\DocumentManagementEmployeeDocumentAttachmentAdapter;
use App\Modules\HR\EmployeeDocuments\Integration\Contracts\EmployeeDocumentAttachmentGateway;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\AttachEmployeeDocumentV1;
use App\Modules\HR\EmployeeDocuments\Integration\Exceptions\EmployeeDocumentAttachmentUnavailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class EmployeeDocumentAttachmentGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_binding_forwards_only_minimal_owner_context_and_returns_available_opaque_reference(): void
    {
        $storage = new InMemoryStorageAdapter;
        $this->app->instance(StorageAdapter::class, $storage);
        $gateway = app(EmployeeDocumentAttachmentGateway::class);
        $this->assertInstanceOf(DocumentManagementEmployeeDocumentAttachmentAdapter::class, $gateway);

        $request = $this->request('employee-document-817-passport');
        $reference = $gateway->createFor($request);
        $replayed = $gateway->createFor($this->request('employee-document-817-passport'));

        $this->assertSame($reference->value(), $replayed->value());
        $this->assertDatabaseCount('dm_documents', 1);
        $this->assertDatabaseCount('dm_document_versions', 1);
        $document = LogicalDocument::query()->firstOrFail();
        $this->assertSame('HR', $document->owner_domain);
        $this->assertSame('EmployeeDocument', $document->owner_aggregate_type);
        $this->assertSame('817', $document->owner_aggregate_id);
        $this->assertSame('AVAILABLE', $document->status);
        $this->assertSame('AVAILABLE', DocumentVersion::query()->firstOrFail()->status);
        $this->assertSame(1, $storage->objectCount());
        $this->assertStringNotContainsString('Siti', $reference->value());
    }

    public function test_unavailable_gateway_fails_closed_without_hr_or_dms_partial_reference(): void
    {
        $this->app->instance(DocumentIngestionGateway::class, new class implements DocumentIngestionGateway
        {
            public function ingest(IngestDocumentV1 $request): IngestionResultV1
            {
                throw new RuntimeException('simulated DMS timeout');
            }
        });
        $gateway = app(EmployeeDocumentAttachmentGateway::class);

        try {
            $gateway->createFor($this->request('employee-document-817-timeout'));
            $this->fail('DMS timeout was accepted.');
        } catch (EmployeeDocumentAttachmentUnavailable) {
            $this->addToAssertionCount(1);
        }

        $this->assertDatabaseCount('dm_documents', 0);
        $this->assertDatabaseCount('dm_document_versions', 0);
        $this->assertDatabaseCount('hr_employee_documents', 0);
    }

    public function test_non_available_result_is_rejected(): void
    {
        $this->app->instance(DocumentIngestionGateway::class, new class implements DocumentIngestionGateway
        {
            public function ingest(IngestDocumentV1 $request): IngestionResultV1
            {
                return new IngestionResultV1(new DmsDocumentReferenceV1('dms_not_available'), 1, 'UNAVAILABLE', 'NOT_CONFIGURED');
            }
        });

        $this->expectException(EmployeeDocumentAttachmentUnavailable::class);
        app(EmployeeDocumentAttachmentGateway::class)->createFor($this->request('employee-document-817-unavailable'));
    }

    public function test_storage_failure_leaves_no_active_dms_or_hr_orphan(): void
    {
        $storage = new InMemoryStorageAdapter('promote');
        $this->app->instance(StorageAdapter::class, $storage);

        try {
            app(EmployeeDocumentAttachmentGateway::class)->createFor($this->request('employee-document-817-storage-failure'));
            $this->fail('Storage failure was accepted.');
        } catch (EmployeeDocumentAttachmentUnavailable) {
            $this->addToAssertionCount(1);
        }

        $this->assertDatabaseCount('dm_documents', 0);
        $this->assertDatabaseCount('dm_document_versions', 0);
        $this->assertDatabaseCount('hr_employee_documents', 0);
        $this->assertSame(0, $storage->objectCount());
    }

    public function test_hr_attachment_boundary_has_no_dms_model_storage_or_employee_profile_dependency(): void
    {
        $root = base_path('app/Modules/HR/EmployeeDocuments/Integration');
        $contents = collect(glob($root.'/{Contracts,DTO,Adapters,Exceptions}/*.php', GLOB_BRACE))
            ->map(fn (string $path): string => file_get_contents($path))
            ->implode("\n");

        $this->assertStringNotContainsString('DocumentManagement\\Foundation\\Models', $contents);
        $this->assertStringNotContainsString('DocumentManagement\\Foundation\\Storage', $contents);
        $this->assertStringNotContainsString('Modules\\HR\\Employees\\Models', $contents);
        $this->assertStringNotContainsString('document_number', $contents);
        $this->assertStringNotContainsString('employee_name', $contents);
    }

    public function test_module_manifests_publish_versioned_attachment_and_ingestion_contracts(): void
    {
        $dms = require base_path('app/Modules/DocumentManagement/Foundation/module.php');
        $hr = require base_path('app/Modules/HR/EmployeeDocuments/module.php');
        $ingestion = collect($dms['integrations']['contracts'])->firstWhere('name', 'DocumentIngestionGateway');
        $attachment = collect($hr['integrations']['contracts'])->firstWhere('name', 'EmployeeDocumentAttachmentGateway');
        $schema = json_decode(
            file_get_contents(base_path('app/Modules/DocumentManagement/Foundation/'.$ingestion['schema'])),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame(1, $ingestion['schema_version']);
        $this->assertSame(DocumentIngestionGateway::class, $ingestion['reader']);
        $this->assertSame('AVAILABLE', $schema['$defs']['result']['properties']['status']['const']);
        $this->assertSame(1, $attachment['schema_version']);
        $this->assertSame(EmployeeDocumentAttachmentGateway::class, $attachment['reader']);
    }

    public function test_attachment_request_rejects_non_readable_stream_at_boundary(): void
    {
        $stream = fopen('php://output', 'w');

        $this->expectException(\InvalidArgumentException::class);
        new AttachEmployeeDocumentV1(817, 'passport.pdf', 'application/pdf', 10, 'key', 'user:41', $stream);
    }

    private function request(string $idempotencyKey): AttachEmployeeDocumentV1
    {
        $contents = "%PDF-1.7\ncontract\n%%EOF\n";
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);

        return new AttachEmployeeDocumentV1(
            817,
            'passport.pdf',
            'application/pdf',
            strlen($contents),
            $idempotencyKey,
            'user:41',
            $stream,
        );
    }
}
