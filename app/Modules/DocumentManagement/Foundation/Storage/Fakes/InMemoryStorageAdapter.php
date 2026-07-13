<?php

namespace App\Modules\DocumentManagement\Foundation\Storage\Fakes;

use App\Modules\DocumentManagement\Foundation\Storage\Contracts\StorageAdapter;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StagedObjectV1;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;
use InvalidArgumentException;
use RuntimeException;

class InMemoryStorageAdapter implements StorageAdapter
{
    /** @var array<string, string> */
    private array $objects = [];

    public function stage(mixed $stream): StagedObjectV1
    {
        $this->assertStream($stream);
        $key = new StorageObjectKeyV1('staged/'.str()->ulid());
        $contents = stream_get_contents($stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read staged stream.');
        }
        $this->objects[$key->value()] = $contents;

        return new StagedObjectV1($key);
    }

    public function read(StorageObjectKeyV1 $key): mixed
    {
        if (! array_key_exists($key->value(), $this->objects)) {
            throw new RuntimeException('Storage object does not exist.');
        }

        $stream = fopen('php://temp', 'w+b');
        if ($stream === false || fwrite($stream, $this->objects[$key->value()]) === false) {
            throw new RuntimeException('Unable to create object read stream.');
        }
        rewind($stream);

        return $stream;
    }

    public function exists(StorageObjectKeyV1 $key): bool
    {
        return array_key_exists($key->value(), $this->objects);
    }

    public function promote(StagedObjectV1 $staged): StorageObjectKeyV1
    {
        if (! $this->exists($staged->key)) {
            throw new RuntimeException('Staged object does not exist.');
        }

        $published = new StorageObjectKeyV1(str_replace('staged/', 'objects/', $staged->key->value()));
        $this->objects[$published->value()] = $this->objects[$staged->key->value()];
        unset($this->objects[$staged->key->value()]);

        return $published;
    }

    public function deleteStaged(StagedObjectV1 $staged): void
    {
        unset($this->objects[$staged->key->value()]);
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
