<?php

namespace App\Modules\HR\HRReports\Queries;

use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use App\Modules\HR\HRReports\DTO\HeadcountReportResult;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class HeadcountReportQuery
{
    public function byDepartement(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->grouped(
            filters: $filters,
            groupBy: 'departement',
            foreignKey: 'departement_id',
            table: 'hr_departements',
        );
    }

    public function byWorkLocation(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->grouped(
            filters: $filters,
            groupBy: 'work_location',
            foreignKey: 'work_location_id',
            table: 'hr_work_locations',
        );
    }

    public function byEmploymentStatus(HeadcountReportFilters $filters): HeadcountReportResult
    {
        return $this->grouped(
            filters: $filters,
            groupBy: 'employment_status',
            foreignKey: 'employment_status_id',
            table: 'hr_employment_statuses',
        );
    }

    private function grouped(
        HeadcountReportFilters $filters,
        string $groupBy,
        string $foreignKey,
        string $table,
    ): HeadcountReportResult {
        $asOf = $filters->asOfDate()->toDateString();

        $rows = $this->baseEmployeeQuery($filters)
            ->leftJoin($table, "{$table}.id", '=', "hr_employees.{$foreignKey}")
            ->selectRaw("{$table}.id as id")
            ->selectRaw("{$table}.code as code")
            ->selectRaw("COALESCE({$table}.name, ?) as name", ['Unassigned'])
            ->selectRaw('COUNT(hr_employees.id) as employee_count')
            ->groupBy("{$table}.id", "{$table}.code", "{$table}.name")
            ->orderByRaw("CASE WHEN {$table}.id IS NULL THEN 1 ELSE 0 END")
            ->orderBy("{$table}.name")
            ->get()
            ->map(fn (object $row) => [
                'id' => $row->id === null ? null : (int) $row->id,
                'code' => $row->code,
                'name' => (string) $row->name,
                'employeeCount' => (int) $row->employee_count,
            ])
            ->values()
            ->all();

        return new HeadcountReportResult(
            asOf: $asOf,
            groupBy: $groupBy,
            rows: $rows,
            total: array_sum(array_column($rows, 'employeeCount')),
        );
    }

    private function baseEmployeeQuery(HeadcountReportFilters $filters): Builder
    {
        $asOf = $filters->asOfDate()->toDateString();

        return DB::table('hr_employees')
            ->whereNull('hr_employees.deleted_at')
            ->where('hr_employees.active', true)
            ->where(function (Builder $query) use ($asOf) {
                $query
                    ->whereNull('hr_employees.hired_at')
                    ->orWhereDate('hr_employees.hired_at', '<=', $asOf);
            })
            ->where(function (Builder $query) use ($asOf) {
                $query
                    ->whereNull('hr_employees.ended_at')
                    ->orWhereDate('hr_employees.ended_at', '>', $asOf);
            })
            ->when($filters->employmentStatusIds !== [], function (Builder $query) use ($filters) {
                $query->whereIn('hr_employees.employment_status_id', $filters->employmentStatusIds);
            });
    }
}
