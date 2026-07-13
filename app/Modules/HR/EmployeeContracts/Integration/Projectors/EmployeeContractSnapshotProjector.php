<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Projectors;

use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractSnapshotReader;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractSnapshotV1;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class EmployeeContractSnapshotProjector implements EmployeeContractSnapshotReader
{
    public function forEmployeeOn(
        int $employeeId,
        CarbonImmutable $effectiveDate,
        CarbonImmutable $capturedAt,
    ): ?EmployeeContractSnapshotV1 {
        $contract = EmployeeContract::query()
            ->with(['employmentType' => fn (BelongsTo $query) => $query->withTrashed()])
            ->forEmployee($employeeId)
            ->whereIn('status', ['ACTIVE', 'ENDED'])
            ->effectiveOn($effectiveDate->toDateString())
            ->latest('start_date')
            ->latest('id')
            ->first();

        if (! $contract || ! $contract->employmentType) {
            return null;
        }

        return new EmployeeContractSnapshotV1(
            employeeId: $contract->employee_id,
            contractId: $contract->id,
            employmentTypeCode: $contract->employmentType->code,
            validFrom: $contract->start_date->toDateString(),
            validUntil: $contract->end_date?->toDateString(),
            capturedAt: $capturedAt,
        );
    }
}
