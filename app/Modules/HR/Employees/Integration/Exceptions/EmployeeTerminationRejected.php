<?php

namespace App\Modules\HR\Employees\Integration\Exceptions;

use RuntimeException;

final class EmployeeTerminationRejected extends RuntimeException
{
    public static function forInvariantViolation(): self
    {
        return new self('Employee termination was rejected by the owner module.');
    }
}
