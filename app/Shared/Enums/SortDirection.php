<?php

namespace App\Shared\Enums;

enum SortDirection: string
{
    case Ascending = 'asc';
    case Descending = 'desc';

    public static function fromNullable(?string $value, self $default = self::Ascending): self
    {
        return self::tryFrom(strtolower((string) $value)) ?? $default;
    }
}
