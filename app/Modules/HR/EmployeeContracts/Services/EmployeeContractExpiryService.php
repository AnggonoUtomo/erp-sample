<?php

namespace App\Modules\HR\EmployeeContracts\Services;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class EmployeeContractExpiryService
{
    /** @return Collection<int, EmployeeContract> */
    public function expiring(CarbonImmutable $date, int $withinDays): Collection
    {
        return $this->applyWindow(EmployeeContract::query(), $date, $withinDays)
            ->with('employee:id,display_name')
            ->orderBy('end_date')
            ->orderBy('id')
            ->get();
    }

    public function applyWindow(Builder $query, CarbonImmutable $date, int $withinDays): Builder
    {
        return $query
            ->where('status', 'ACTIVE')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $date->toDateString())
            ->whereDate('end_date', '<=', $date->addDays($withinDays)->toDateString());
    }
}
