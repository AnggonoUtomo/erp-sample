<?php

namespace App\Modules\HR\EmploymentStatuses\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\EmploymentStatuses\DTO\EmploymentStatusData;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentStatuses\Transactions\EmploymentStatusesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmploymentStatusesService
{
    private const AUDIT_FIELDS = [
        'code',
        'name',
        'description',
        'requires_attendance',
        'included_in_payroll',
        'is_final_status',
        'active',
        'sort_order',
    ];

    public function __construct(
        private readonly EmploymentStatusesTransaction $transaction,
        private readonly AuditLogService $audit,
        private readonly SystemSettingService $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPageData(array $filters = []): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $status = $filters['status'] ?? 'all';
        $archive = $filters['archive'] ?? 'active';
        $perPage = $this->resolvePerPage($filters);

        $employmentStatuses = EmploymentStatus::query()
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'employmentStatuses' => $this->transformPaginator($employmentStatuses),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'archive' => $archive,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => EmploymentStatus::query()->count(),
                'active' => EmploymentStatus::query()->where('active', true)->count(),
                'inactive' => EmploymentStatus::query()->where('active', false)->count(),
                'is_final_status' => EmploymentStatus::query()->where('is_final_status', true)->count(),
                'archived' => EmploymentStatus::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(EmploymentStatusData $data): EmploymentStatus
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $employmentStatus = EmploymentStatus::query()->create($payload);

            $this->audit->record(
                module: 'hr.employment-statuses',
                event: 'EmploymentStatus.created',
                auditable: $employmentStatus,
                description: "Created Employment Status {$employmentStatus->code}",
                newValues: $employmentStatus->only(self::AUDIT_FIELDS),
            );

            return $employmentStatus->refresh();
        });
    }

    public function update(EmploymentStatus $employmentStatus, EmploymentStatusData $data): EmploymentStatus
    {
        return $this->transaction->run(function () use ($employmentStatus, $data) {
            $oldValues = $employmentStatus->only(self::AUDIT_FIELDS);

            $employmentStatus->update($this->payload($data));

            $this->audit->record(
                module: 'hr.employment-statuses',
                event: 'EmploymentStatus.updated',
                auditable: $employmentStatus,
                description: "Updated Employment Status {$employmentStatus->code}",
                oldValues: $oldValues,
                newValues: $employmentStatus->only(self::AUDIT_FIELDS),
            );

            return $employmentStatus->refresh();
        });
    }

    public function delete(EmploymentStatus $employmentStatus): void
    {
        $this->transaction->run(function () use ($employmentStatus) {
            $oldValues = $employmentStatus->only(self::AUDIT_FIELDS);

            $employmentStatus->delete();

            $this->audit->record(
                module: 'hr.employment-statuses',
                event: 'EmploymentStatus.deleted',
                auditable: $employmentStatus,
                description: "Archived Employment Status {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(EmploymentStatus $employmentStatus): void
    {
        $this->transaction->run(function () use ($employmentStatus) {
            $oldValues = $employmentStatus->only(self::AUDIT_FIELDS);

            $employmentStatus->restore();

            $this->audit->record(
                module: 'hr.employment-statuses',
                event: 'EmploymentStatus.restored',
                auditable: $employmentStatus,
                description: "Restored Employment Status {$employmentStatus->code}",
                oldValues: $oldValues,
                newValues: $employmentStatus->fresh()?->only(self::AUDIT_FIELDS),
            );
        });
    }

    public function forceDelete(EmploymentStatus $employmentStatus): void
    {
        $this->transaction->run(function () use ($employmentStatus) {
            $oldValues = $employmentStatus->only(self::AUDIT_FIELDS);

            $this->audit->record(
                module: 'hr.employment-statuses',
                event: 'EmploymentStatus.force-deleted',
                auditable: $employmentStatus,
                description: "Force deleted Employment Status {$oldValues['code']}",
                oldValues: $oldValues,
            );

            $employmentStatus->forceDelete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(EmploymentStatusData $data): array
    {
        $payload = [
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'requires_attendance' => $data->requiresAttendance,
            'included_in_payroll' => $data->includedInPayroll,
            'is_final_status' => $data->isFinalStatus,
            'active' => $data->active,
        ];

        if ($data->sortOrder !== null) {
            $payload['sort_order'] = $data->sortOrder;
        }

        return $payload;
    }

    private function nextSortOrder(): int
    {
        return ((int) EmploymentStatus::query()->max('sort_order')) + 1;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function resolvePerPage(array $filters): int
    {
        $settings = $this->settings->paginationSettings();
        $default = (int) $settings['default_per_page'];
        $options = $settings['per_page_options'];
        $perPage = (int) ($filters['per_page'] ?? $default);

        return in_array($perPage, $options, true) ? $perPage : $default;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPaginator(LengthAwarePaginator $employmentStatuses): array
    {
        return [
            'data' => collect($employmentStatuses->items())
                ->map(fn (EmploymentStatus $employmentStatus) => [
                    'id' => $employmentStatus->id,
                    'code' => $employmentStatus->code,
                    'name' => $employmentStatus->name,
                    'description' => $employmentStatus->description,
                    'requires_attendance' => $employmentStatus->requires_attendance,
                    'included_in_payroll' => $employmentStatus->included_in_payroll,
                    'is_final_status' => $employmentStatus->is_final_status,
                    'active' => $employmentStatus->active,
                    'sort_order' => $employmentStatus->sort_order,
                    'deleted_at' => $employmentStatus->deleted_at?->toISOString(),
                    'created_at' => $employmentStatus->created_at?->toISOString(),
                    'updated_at' => $employmentStatus->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $employmentStatuses->currentPage(),
            'last_page' => $employmentStatuses->lastPage(),
            'per_page' => $employmentStatuses->perPage(),
            'total' => $employmentStatuses->total(),
            'from' => $employmentStatuses->firstItem(),
            'to' => $employmentStatuses->lastItem(),
        ];
    }
}
