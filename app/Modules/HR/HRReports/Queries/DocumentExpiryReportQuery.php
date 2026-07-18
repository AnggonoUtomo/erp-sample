<?php

namespace App\Modules\HR\HRReports\Queries;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\DTO\ExpiryReportResult;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class DocumentExpiryReportQuery
{
    public function expiring(ExpiryReportFilters $filters): ExpiryReportResult
    {
        $asOf = $filters->asOfDate();
        $through = $asOf->addDays($filters->withinDays);

        $rows = DB::table('hr_employee_documents')
            ->join('hr_employees', 'hr_employees.id', '=', 'hr_employee_documents.employee_id')
            ->join('hr_reference_data', 'hr_reference_data.id', '=', 'hr_employee_documents.document_type_id')
            ->whereNull('hr_employee_documents.deleted_at')
            ->whereNull('hr_employees.deleted_at')
            ->whereNull('hr_reference_data.deleted_at')
            ->whereNotNull('hr_employee_documents.expires_at')
            ->whereDate('hr_employee_documents.expires_at', '<=', $through->toDateString())
            ->select([
                'hr_employees.id as employee_id',
                'hr_employees.employee_number',
                'hr_employees.display_name as employee_name',
                'hr_reference_data.name as type_label',
                'hr_employee_documents.expires_at as expires_at',
            ])
            ->orderBy('hr_employee_documents.expires_at')
            ->orderBy('hr_employee_documents.id')
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
