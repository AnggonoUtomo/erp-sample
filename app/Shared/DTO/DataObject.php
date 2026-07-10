<?php

namespace App\Shared\DTO;

use App\Shared\Contracts\ArrayableData;
use Illuminate\Contracts\Support\Arrayable;

/**
 * @implements Arrayable<string, mixed>
 */
abstract readonly class DataObject implements ArrayableData, Arrayable
{
    /**
     * @param  array<string, mixed>  $payload
     */
    abstract public static function fromArray(array $payload): static;

    /**
     * @param  iterable<array<string, mixed>>  $items
     * @return array<int, static>
     */
    public static function collect(iterable $items): array
    {
        $collection = [];

        foreach ($items as $item) {
            $collection[] = static::fromArray($item);
        }

        return $collection;
    }
}
