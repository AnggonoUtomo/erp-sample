<?php

use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Integration\DTO\DocumentReferenceDescriptorV1;
use App\Modules\DocumentManagement\Foundation\Providers\FoundationServiceProvider;

return [
    'name' => 'Foundation',
    'project' => 'DocumentManagement',
    'title' => 'Document Management Foundation',
    'slug' => 'foundation',
    'description' => 'Contract-only foundation for private, versioned document ownership and access.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        FoundationServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => false,
        'permissions' => true,
        'navigation' => false,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [
        'contracts' => [[
            'name' => 'DocumentReferenceReader',
            'schema_version' => 1,
            'reader' => DocumentReferenceReader::class,
            'schema' => 'Integration/Schemas/document-owner-context-v1.json',
            'states' => DocumentReferenceDescriptorV1::STATES,
        ]],
    ],
];
