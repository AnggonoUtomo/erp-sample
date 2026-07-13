<?php

namespace Tests\Feature;

use App\Modules\HR\EmployeeDocuments\Integration\Contracts\DocumentReferenceReader;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\DocumentReferenceV1;
use App\Modules\HR\EmployeeDocuments\Integration\DTO\EmployeeDocumentOwnerContextV1;
use App\Modules\HR\EmployeeDocuments\Integration\Fakes\InMemoryDocumentReferenceReader;
use InvalidArgumentException;
use Tests\TestCase;

class EmployeeDocumentDmsContractTest extends TestCase
{
    public function test_owner_context_v1_contains_only_stable_minimal_fields(): void
    {
        $context = EmployeeDocumentOwnerContextV1::forDocument(817);

        $this->assertSame([
            'schemaVersion' => 1,
            'domain' => 'HR',
            'aggregateType' => 'EmployeeDocument',
            'aggregateId' => '817',
        ], $context->toArray());
        $this->assertSame(
            ['schemaVersion', 'domain', 'aggregateType', 'aggregateId'],
            array_keys($context->toArray()),
        );
    }

    public function test_reference_is_opaque_and_contract_never_exposes_storage_location(): void
    {
        $reference = new DocumentReferenceV1('dms:v1:opaque/segment?still=data');
        $descriptor = new DocumentReferenceDescriptorV1($reference, 'AVAILABLE');

        $this->assertSame('dms:v1:opaque/segment?still=data', $reference->value());
        $this->assertSame([
            'schemaVersion' => 1,
            'reference' => 'dms:v1:opaque/segment?still=data',
            'state' => 'AVAILABLE',
        ], $descriptor->toArray());
        $this->assertEmpty(array_intersect(
            ['path', 'url', 'disk', 'mediaId', 'signedUrl', 'blobId'],
            array_keys($descriptor->toArray()),
        ));
    }

    public function test_fake_reader_has_explicit_missing_archived_denied_and_unavailable_semantics(): void
    {
        $reader = new InMemoryDocumentReferenceReader(
            statesByReference: ['available-ref' => 'AVAILABLE', 'archived-ref' => 'ARCHIVED'],
            deniedActorReferences: ['actor-denied'],
        );
        $this->assertInstanceOf(DocumentReferenceReader::class, $reader);

        $this->assertSame('AVAILABLE', $reader->describe(new DocumentReferenceV1('available-ref'), 'actor-1')->state);
        $this->assertSame('ARCHIVED', $reader->describe(new DocumentReferenceV1('archived-ref'), 'actor-1')->state);
        $this->assertSame('MISSING', $reader->describe(new DocumentReferenceV1('missing-ref'), 'actor-1')->state);
        $this->assertSame('DENIED', $reader->describe(new DocumentReferenceV1('available-ref'), 'actor-denied')->state);

        $reader->setUnavailable(true);
        $this->assertSame('UNAVAILABLE', $reader->describe(new DocumentReferenceV1('available-ref'), 'actor-1')->state);
    }

    public function test_invalid_reference_state_and_owner_id_are_rejected_at_contract_boundary(): void
    {
        foreach ([
            fn () => new DocumentReferenceV1(''),
            fn () => new DocumentReferenceDescriptorV1(new DocumentReferenceV1('ref'), 'UNKNOWN'),
            fn () => EmployeeDocumentOwnerContextV1::forDocument(0),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Invalid contract value was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_module_metadata_and_normative_schema_publish_contract_v1(): void
    {
        $module = require base_path('app/Modules/HR/EmployeeDocuments/module.php');
        $contract = collect($module['integrations']['contracts'])->firstWhere('name', 'DocumentReferenceReader');
        $schemaPath = base_path('app/Modules/HR/EmployeeDocuments/'.$contract['schema']);
        $schema = json_decode(file_get_contents($schemaPath), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame(1, $contract['schema_version']);
        $this->assertSame(DocumentReferenceReader::class, $contract['reader']);
        $this->assertSame(1, $schema['properties']['schemaVersion']['const']);
        $this->assertSame(['schemaVersion', 'domain', 'aggregateType', 'aggregateId'], $schema['required']);
        $this->assertFalse($schema['additionalProperties']);
        $this->assertSame(DocumentReferenceDescriptorV1::STATES, $contract['states']);
        $this->assertSame(DocumentReferenceDescriptorV1::STATES, $schema['$defs']['documentReferenceDescriptor']['properties']['state']['enum']);
    }
}
