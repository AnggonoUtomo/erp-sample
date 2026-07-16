<?php

namespace App\Modules\HR\Employees\Integration\Contracts;

use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationCommandV1;
use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationResultV1;

interface EmployeeTerminationGateway
{
    public function terminate(EmployeeTerminationCommandV1 $command): EmployeeTerminationResultV1;
}
