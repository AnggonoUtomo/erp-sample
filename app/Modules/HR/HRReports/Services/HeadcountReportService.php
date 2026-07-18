<?php

namespace App\Modules\HR\HRReports\Services;

use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use App\Modules\HR\HRReports\DTO\HeadcountReportResult;
use App\Modules\HR\HRReports\Queries\HeadcountReportQuery;

final readonly class HeadcountReportService
{
    public function __construct(
        private HeadcountReportQuery $query,
    ) {}

    public function byDepartement(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->query->byDepartement($filters);
    }

    public function byWorkLocation(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->query->byWorkLocation($filters);
    }

    public function byEmploymentStatus(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->query->byEmploymentStatus($filters);
    }
}
