<?php

namespace App\Modules\DocumentManagement\Foundation\Storage\Contracts;

use App\Modules\DocumentManagement\Foundation\Storage\DTO\StagedObjectV1;
use App\Modules\DocumentManagement\Foundation\Storage\DTO\StorageObjectKeyV1;

interface StorageAdapter
{
    public function stage(mixed $stream): StagedObjectV1;

    /** @return resource */
    public function read(StorageObjectKeyV1 $key): mixed;

    public function exists(StorageObjectKeyV1 $key): bool;

    public function promote(StagedObjectV1 $staged): StorageObjectKeyV1;

    public function deleteStaged(StagedObjectV1 $staged): void;
}
