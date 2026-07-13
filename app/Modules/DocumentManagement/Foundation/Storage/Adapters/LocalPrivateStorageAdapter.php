<?php

namespace App\Modules\DocumentManagement\Foundation\Storage\Adapters;

use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StagedObjectV1;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class LocalPrivateStorageAdapter implements StorageAdapter
{
    public function __construct(
        private FilesystemFactory $filesystems,
        private string $diskName,
    ) {
        $configuration = config("filesystems.disks.{$diskName}");
        if (! is_array($configuration)
            || ($configuration['driver'] ?? null) !== 'local'
            || ($configuration['serve'] ?? null) !== false
            || ($configuration['visibility'] ?? null) !== 'private'
            || array_key_exists('url', $configuration)) {
            throw new InvalidArgumentException('Document storage disk must be a non-served private local disk.');
        }
    }

    public function stage(mixed $stream): StagedObjectV1
    {
        $this->assertStream($stream);
        $key = new StorageObjectKeyV1('staged/'.str()->ulid());

        try {
            if (! $this->disk()->writeStream($key->value(), $stream)) {
                throw new RuntimeException('Unable to write staged object.');
            }
        } catch (Throwable $exception) {
            try {
                if ($this->disk()->exists($key->value())) {
                    $this->disk()->delete($key->value());
                }
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
            throw $exception;
        }

        return new StagedObjectV1($key);
    }

    public function read(StorageObjectKeyV1 $key): mixed
    {
        $stream = $this->disk()->readStream($key->value());
        if (! is_resource($stream)) {
            throw new RuntimeException('Unable to read storage object.');
        }

        return $stream;
    }

    public function exists(StorageObjectKeyV1 $key): bool
    {
        return $this->disk()->exists($key->value());
    }

    public function promote(StagedObjectV1 $staged): StorageObjectKeyV1
    {
        if (! $this->exists($staged->key)) {
            throw new RuntimeException('Staged object does not exist.');
        }

        $published = new StorageObjectKeyV1(str_replace('staged/', 'objects/', $staged->key->value()));
        try {
            if (! $this->disk()->move($staged->key->value(), $published->value())) {
                throw new RuntimeException('Unable to promote staged object.');
            }
        } catch (Throwable $exception) {
            try {
                if ($this->disk()->exists($published->value())) {
                    $this->disk()->delete($published->value());
                }
            } catch (Throwable $cleanupException) {
                report($cleanupException);
            }
            throw $exception;
        }

        return $published;
    }

    public function deleteStaged(StagedObjectV1 $staged): void
    {
        if ($this->exists($staged->key) && ! $this->disk()->delete($staged->key->value())) {
            throw new RuntimeException('Unable to delete staged object.');
        }
    }

    public function deleteFailedObject(StorageObjectKeyV1 $key): void
    {
        if ($this->exists($key) && ! $this->disk()->delete($key->value())) {
            throw new RuntimeException('Unable to delete failed storage object.');
        }
    }

    private function disk(): Filesystem
    {
        return $this->filesystems->disk($this->diskName);
    }

    private function assertStream(mixed $stream): void
    {
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Storage input must be a readable stream.');
        }

        $mode = (string) (stream_get_meta_data($stream)['mode'] ?? '');
        if (! str_contains($mode, 'r') && ! str_contains($mode, '+')) {
            throw new InvalidArgumentException('Storage input must be a readable stream.');
        }
    }
}
