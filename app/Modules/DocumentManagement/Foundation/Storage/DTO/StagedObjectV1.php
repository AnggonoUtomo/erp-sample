<?php

namespace App\Modules\DocumentManagement\Foundation\Storage\DTO;

use InvalidArgumentException;

final readonly class StagedObjectV1
{
    public function __construct(public StorageObjectKeyV1 $key)
    {
        if (! $key->isStaged()) {
            throw new InvalidArgumentException('Staged object requires a staged storage key.');
        }
    }
}
