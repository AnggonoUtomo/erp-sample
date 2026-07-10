<?php

namespace App\Shared\Exceptions;

use RuntimeException;

class SharedKernelException extends RuntimeException
{
    public static function invalidValueObject(string $valueObject, string $reason): self
    {
        return new self("Invalid {$valueObject}: {$reason}");
    }
}
