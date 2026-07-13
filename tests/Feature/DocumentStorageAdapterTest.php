<?php

namespace Tests\Feature;

use App\Modules\DocumentManagement\Foundation\Storage\Adapters\LocalPrivateStorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use App\Modules\DocumentManagement\Foundation\Storage\Fakes\InMemoryStorageAdapter;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use ReflectionClass;
use Tests\TestCase;

class DocumentStorageAdapterTest extends TestCase
{
    private string $localRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localRoot = storage_path('framework/testing/dms-private-'.str()->uuid());
        config([
            'filesystems.disks.dms-private' => [
                'driver' => 'local',
                'root' => $this->localRoot,
                'serve' => false,
                'visibility' => 'private',
                'throw' => true,
            ],
        ]);
        Storage::forgetDisk('dms-private');
    }

    protected function tearDown(): void
    {
        Storage::forgetDisk('dms-private');
        File::deleteDirectory($this->localRoot);

        parent::tearDown();
    }

    public function test_fake_and_local_adapters_share_staged_promote_read_contract(): void
    {
        foreach ($this->adapters() as $name => $adapter) {
            $stream = fopen('php://temp', 'w+b');
            fwrite($stream, "private-content-{$name}");
            rewind($stream);

            $staged = $adapter->stage($stream);
            fclose($stream);

            $this->assertMatchesRegularExpression(
                '#^staged/[0-9A-HJKMNP-TV-Z]{26}$#',
                $staged->key->value(),
                $name,
            );
            $this->assertTrue($adapter->exists($staged->key), $name);

            $published = $adapter->promote($staged);

            $this->assertMatchesRegularExpression(
                '#^objects/[0-9A-HJKMNP-TV-Z]{26}$#',
                $published->value(),
                $name,
            );
            $this->assertFalse($adapter->exists($staged->key), $name);
            $this->assertTrue($adapter->exists($published), $name);

            $read = $adapter->read($published);
            $this->assertIsResource($read, $name);
            $this->assertSame("private-content-{$name}", stream_get_contents($read), $name);
            fclose($read);
        }
    }

    public function test_delete_staged_is_idempotent_and_never_deletes_published_object(): void
    {
        foreach ($this->adapters() as $name => $adapter) {
            $firstStream = $this->stream('published');
            $published = $adapter->promote($adapter->stage($firstStream));
            fclose($firstStream);
            $secondStream = $this->stream('discarded');
            $discarded = $adapter->stage($secondStream);
            fclose($secondStream);

            $adapter->deleteStaged($discarded);
            $adapter->deleteStaged($discarded);

            $this->assertFalse($adapter->exists($discarded->key), $name);
            $this->assertTrue($adapter->exists($published), $name);
        }
    }

    public function test_contract_rejects_caller_paths_invalid_streams_and_public_url_capability(): void
    {
        foreach ([
            '', '../secret', 'public/file.pdf', 'staged/file.pdf',
            'objects/../../secret', 'objects/01J00000000000000000000000/extra',
        ] as $key) {
            try {
                new StorageObjectKeyV1($key);
                $this->fail("Unsafe storage key was accepted: {$key}");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }

        foreach ($this->adapters() as $adapter) {
            try {
                $adapter->stage('not-a-stream');
                $this->fail('Non-stream input was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }

            $writeOnlyPath = tempnam(sys_get_temp_dir(), 'dms-write-only-');
            $writeOnly = fopen($writeOnlyPath, 'wb');
            try {
                $adapter->stage($writeOnly);
                $this->fail('Write-only stream was accepted.');
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            } finally {
                fclose($writeOnly);
                unlink($writeOnlyPath);
            }
        }

        $methods = collect((new ReflectionClass(StorageAdapter::class))->getMethods())->pluck('name');
        $this->assertEmpty($methods->intersect(['url', 'temporaryUrl', 'publicUrl', 'path']));
        $this->assertFalse(config('filesystems.disks.dms-private.serve'));
        $this->assertSame('private', config('filesystems.disks.dms-private.visibility'));
        $this->assertArrayNotHasKey('url', config('filesystems.disks.dms-private'));
    }

    public function test_local_adapter_fails_closed_for_public_or_served_disk_configuration(): void
    {
        config([
            'filesystems.disks.unsafe-dms' => [
                'driver' => 'local',
                'root' => $this->localRoot,
                'serve' => true,
                'visibility' => 'public',
                'url' => 'https://example.test/files',
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);

        new LocalPrivateStorageAdapter(app(FilesystemFactory::class), 'unsafe-dms');
    }

    public function test_container_uses_the_private_local_adapter_by_default(): void
    {
        $this->assertInstanceOf(LocalPrivateStorageAdapter::class, app(StorageAdapter::class));
    }

    /** @return array<string, StorageAdapter> */
    private function adapters(): array
    {
        return [
            'fake' => new InMemoryStorageAdapter,
            'local' => new LocalPrivateStorageAdapter(app(FilesystemFactory::class), 'dms-private'),
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
