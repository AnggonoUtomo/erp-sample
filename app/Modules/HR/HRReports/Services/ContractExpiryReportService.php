<?php

namespace App\Modules\HR\HRReports\Services;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\DTO\ExpiryReportResult;
use App\Modules\HR\HRReports\Queries\ContractExpiryReportQuery;

final readonly class ContractExpiryReportService
{
    public function __construct(
        private ContractExpiryReportQuery $query,
    ) {}

    public function expiring(ExpiryReportFilters $filters): ExpiryReportResult
    {
        return $this->query->expiring($filters);
    }
}
