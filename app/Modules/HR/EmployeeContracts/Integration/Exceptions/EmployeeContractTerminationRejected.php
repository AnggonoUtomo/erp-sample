<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Exceptions;

use RuntimeException;

final class EmployeeContractTerminationRejected extends RuntimeException
{
    public static function forInvariantViolation(): self
    {
        return new self('Employee contract termination was rejected by the owner module.');
    }
}
