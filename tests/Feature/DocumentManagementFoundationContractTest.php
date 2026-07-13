<?php

namespace Tests\Feature;

use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentOwnerContextV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceV1;
use App\Support\Modules\ModuleContractValidator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class DocumentManagementFoundationContractTest extends TestCase
{
    public function test_module_manifest_is_valid_and_does_not_export_routes_or_navigation(): void
    {
        $module = require base_path('app/Modules/DocumentManagement/Foundation/module.php');

        $this->assertSame('DocumentManagement', $module['project']);
        $this->assertSame('Foundation', $module['name']);
        $this->assertFalse($module['exports']['routes']);
        $this->assertTrue($module['exports']['permissions']);
        $this->assertFalse($module['exports']['navigation']);
        $this->assertSame([], app(ModuleContractValidator::class)->validate('DocumentManagement.Foundation'));
    }

    public function test_owner_context_v1_is_minimal_versioned_and_rejects_invalid_values(): void
    {
        $context = new DocumentOwnerContextV1('HR', 'EmployeeDocument', '817');

        $this->assertSame([
            'schemaVersion' => 1,
            'domain' => 'HR',
            'aggregateType' => 'EmployeeDocument',
            'aggregateId' => '817',
        ], $context->toArray());

        foreach ([
            fn () => new DocumentOwnerContextV1('', 'EmployeeDocument', '817'),
            fn () => new DocumentOwnerContextV1('HR', '', '817'),
            fn () => new DocumentOwnerContextV1('HR', 'EmployeeDocument', ''),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Invalid owner context was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_reference_and_descriptor_are_opaque_fail_closed_contracts(): void
    {
        $reference = new DocumentReferenceV1('dms:v1:opaque/segment?still=data');
        $descriptor = new DocumentReferenceDescriptorV1($reference, 'AVAILABLE');

        $this->assertSame([
            'schemaVersion' => 1,
            'reference' => 'dms:v1:opaque/segment?still=data',
            'state' => 'AVAILABLE',
        ], $descriptor->toArray());
        $this->assertEmpty(array_intersect(
            ['path', 'url', 'disk', 'objectKey', 'storageKey', 'signedUrl'],
            array_keys($descriptor->toArray()),
        ));
        $this->assertSame(
            ['AVAILABLE', 'MISSING', 'ARCHIVED', 'UNAVAILABLE', 'DENIED'],
            DocumentReferenceDescriptorV1::STATES,
        );

        foreach ([
            fn () => new DocumentReferenceV1(''),
            fn () => new DocumentReferenceV1(str_repeat('x', 256)),
            fn () => new DocumentReferenceDescriptorV1($reference, 'UNKNOWN'),
        ] as $operation) {
            try {
                $operation();
                $this->fail('Invalid reference contract value was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_manifest_and_schema_publish_the_same_contract_v1(): void
    {
        $module = require base_path('app/Modules/DocumentManagement/Foundation/module.php');
        $contract = collect($module['integrations']['contracts'])->firstWhere('name', 'DocumentReferenceReader');
        $schema = json_decode(
            file_get_contents(base_path('app/Modules/DocumentManagement/Foundation/'.$contract['schema'])),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame(1, $contract['schema_version']);
        $this->assertSame(DocumentReferenceReader::class, $contract['reader']);
        $this->assertSame(1, $schema['properties']['schemaVersion']['const']);
        $this->assertSame(['schemaVersion', 'domain', 'aggregateType', 'aggregateId'], $schema['required']);
        $this->assertFalse($schema['additionalProperties']);
        $this->assertSame(DocumentReferenceDescriptorV1::STATES, $contract['states']);
        $this->assertSame(
            DocumentReferenceDescriptorV1::STATES,
            $schema['$defs']['documentReferenceDescriptor']['properties']['state']['enum'],
        );
    }

    public function test_foundation_contract_has_no_storage_or_hr_internal_dependency(): void
    {
        $root = base_path('app/Modules/DocumentManagement/Foundation');
        $forbiddenImports = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $contents = file_get_contents($file->getPathname());
                if (str_contains($contents, 'App\\Modules\\HR\\') || str_contains($contents, 'Storage::url(')) {
                    $forbiddenImports[] = $file->getPathname();
                }
            }
        }

        $this->assertDirectoryDoesNotExist($root.'/Database');
        $this->assertFileDoesNotExist($root.'/routes.php');
        $this->assertSame([], $forbiddenImports);
    }
}
