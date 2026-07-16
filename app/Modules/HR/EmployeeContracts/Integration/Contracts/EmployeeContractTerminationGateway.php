<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Contracts;

use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationCommandV1;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationResultV1;

interface EmployeeContractTerminationGateway
{
    public function terminate(EmployeeContractTerminationCommandV1 $command): EmployeeContractTerminationResultV1;
}
