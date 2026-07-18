<?php

namespace App\Modules\HR\HRReports\Queries;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\DTO\ExpiryReportResult;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class ContractExpiryReportQuery
{
    public function expiring(ExpiryReportFilters $filters): ExpiryReportResult
    {
        $asOf = $filters->asOfDate();
        $through = $asOf->addDays($filters->withinDays);

        $rows = DB::table('hr_employee_contracts')
            ->join('hr_employees', 'hr_employees.id', '=', 'hr_employee_contracts.employee_id')
            ->join('hr_employment_types', 'hr_employment_types.id', '=', 'hr_employee_contracts.employment_type_id')
            ->whereNull('hr_employee_contracts.deleted_at')
            ->whereNull('hr_employees.deleted_at')
            ->whereNull('hr_employment_types.deleted_at')
            ->where('hr_employee_contracts.status', 'ACTIVE')
            ->whereNotNull('hr_employee_contracts.end_date')
            ->whereDate('hr_employee_contracts.end_date', '<=', $through->toDateString())
            ->select([
                'hr_employees.id as employee_id',
                'hr_employees.employee_number',
                'hr_employees.display_name as employee_name',
                'hr_employment_types.name as type_label',
                'hr_employee_contracts.end_date as expires_at',
            ])
            ->orderBy('hr_employee_contracts.end_date')
            ->orderBy('hr_employee_contracts.id')
            ->get()
            ->map(function (object $row) use ($asOf): array {
                $expiresAt = CarbonImmutable::createFromFormat('!Y-m-d', substr((string) $row->expires_at, 0, 10));
                $daysRemaining = $asOf->diffInDays($expiresAt, false);

                return [
                    'employeeId' => (int) $row->employee_id,
                    'employeeNumber' => (string) $row->employee_number,
                    'employeeName' => (string) $row->employee_name,
                    'typeLabel' => (string) $row->type_label,
                    'expiresAt' => $expiresAt->toDateString(),
                    'daysRemaining' => (int) $daysRemaining,
                    'state' => $daysRemaining < 0 ? 'EXPIRED' : 'EXPIRING',
                ];
            })
            ->values()
            ->all();

        return new ExpiryReportResult(
            asOf: $asOf->toDateString(),
            withinDays: $filters->withinDays,
            rows: $rows,
            total: count($rows),
        );
    }
}
