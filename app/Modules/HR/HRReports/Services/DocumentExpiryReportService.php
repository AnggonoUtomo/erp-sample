<?php

namespace App\Modules\HR\HRReports\Services;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\DTO\ExpiryReportResult;
use App\Modules\HR\HRReports\Queries\DocumentExpiryReportQuery;

final readonly class DocumentExpiryReportService
{
    public function __construct(
        private DocumentExpiryReportQuery $query,
    ) {}

    public function expiring(ExpiryReportFilters $filters): ExpiryReportResult
    {
        return $this->query->expiring($filters);
    }
}
