<?php

namespace App\Modules\DocumentManagement\Foundation\Providers;

use App\Modules\DocumentManagement\Foundation\Integration\Contracts\DocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Lifecycle\Readers\DatabaseDocumentReferenceReader;
use App\Modules\DocumentManagement\Foundation\Storage\Adapters\LocalPrivateStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Upload\Contracts\FileSignatureDetector;
use App\Modules\DocumentManagement\Foundation\Upload\Detectors\BoundedMagicByteDetector;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StorageAdapter::class, fn ($app): StorageAdapter => new LocalPrivateStorageAdapter(
            $app->make(FilesystemFactory::class),
            (string) config('document-management.storage_disk'),
        ));
        $this->app->bind(FileSignatureDetector::class, BoundedMagicByteDetector::class);
        $this->app->bind(DocumentReferenceReader::class, DatabaseDocumentReferenceReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
