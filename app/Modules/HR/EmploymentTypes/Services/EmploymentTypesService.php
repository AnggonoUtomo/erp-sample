<?php

namespace App\Modules\HR\EmploymentTypes\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\EmploymentTypes\DTO\EmploymentTypeData;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\EmploymentTypes\Transactions\EmploymentTypesTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EmploymentTypesService
{
    private const AUDIT_FIELDS = [
        'code',
        'name',
        'description',
        'requires_contract_end_date',
        'included_in_payroll',
        'eligible_for_benefits',
        'eligible_for_overtime',
        'active',
        'sort_order',
    ];

    public function __construct(
        private readonly EmploymentTypesTransaction $transaction,
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

        $employmentTypes = EmploymentType::query()
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
            'employmentTypes' => $this->transformPaginator($employmentTypes),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'archive' => $archive,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => EmploymentType::query()->count(),
                'active' => EmploymentType::query()->where('active', true)->count(),
                'inactive' => EmploymentType::query()->where('active', false)->count(),
                'requires_contract_end_date' => EmploymentType::query()->where('requires_contract_end_date', true)->count(),
                'eligible_for_benefits' => EmploymentType::query()->where('eligible_for_benefits', true)->count(),
                'archived' => EmploymentType::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(EmploymentTypeData $data): EmploymentType
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $employmentType = EmploymentType::query()->create($payload);

            $this->audit->record(
                module: 'hr.employment-types',
                event: 'EmploymentType.created',
                auditable: $employmentType,
                description: "Created Employment Type {$employmentType->code}",
                newValues: $employmentType->only(self::AUDIT_FIELDS),
            );

            return $employmentType->refresh();
        });
    }

    public function update(EmploymentType $employmentType, EmploymentTypeData $data): EmploymentType
    {
        return $this->transaction->run(function () use ($employmentType, $data) {
            $oldValues = $employmentType->only(self::AUDIT_FIELDS);

            $employmentType->update($this->payload($data));

            $this->audit->record(
                module: 'hr.employment-types',
                event: 'EmploymentType.updated',
                auditable: $employmentType,
                description: "Updated Employment Type {$employmentType->code}",
                oldValues: $oldValues,
                newValues: $employmentType->only(self::AUDIT_FIELDS),
            );

            return $employmentType->refresh();
        });
    }

    public function delete(EmploymentType $employmentType): void
    {
        $this->transaction->run(function () use ($employmentType) {
            $oldValues = $employmentType->only(self::AUDIT_FIELDS);

            $employmentType->delete();

            $this->audit->record(
                module: 'hr.employment-types',
                event: 'EmploymentType.deleted',
                auditable: $employmentType,
                description: "Archived Employment Type {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(EmploymentType $employmentType): void
    {
        $this->transaction->run(function () use ($employmentType) {
            $oldValues = $employmentType->only(self::AUDIT_FIELDS);

            $employmentType->restore();

            $this->audit->record(
                module: 'hr.employment-types',
                event: 'EmploymentType.restored',
                auditable: $employmentType,
                description: "Restored Employment Type {$employmentType->code}",
                oldValues: $oldValues,
                newValues: $employmentType->fresh()?->only(self::AUDIT_FIELDS),
            );
        });
    }

    public function forceDelete(EmploymentType $employmentType): void
    {
        $this->transaction->run(function () use ($employmentType) {
            $oldValues = $employmentType->only(self::AUDIT_FIELDS);

            $this->audit->record(
                module: 'hr.employment-types',
                event: 'EmploymentType.force-deleted',
                auditable: $employmentType,
                description: "Force deleted Employment Type {$oldValues['code']}",
                oldValues: $oldValues,
            );

            $employmentType->forceDelete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(EmploymentTypeData $data): array
    {
        $payload = [
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'requires_contract_end_date' => $data->requiresContractEndDate,
            'included_in_payroll' => $data->includedInPayroll,
            'eligible_for_benefits' => $data->eligibleForBenefits,
            'eligible_for_overtime' => $data->eligibleForOvertime,
            'active' => $data->active,
        ];

        if ($data->sortOrder !== null) {
            $payload['sort_order'] = $data->sortOrder;
        }

        return $payload;
    }

    private function nextSortOrder(): int
    {
        return ((int) EmploymentType::query()->max('sort_order')) + 1;
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
    private function transformPaginator(LengthAwarePaginator $employmentTypes): array
    {
        return [
            'data' => collect($employmentTypes->items())
                ->map(fn (EmploymentType $employmentType) => [
                    'id' => $employmentType->id,
                    'code' => $employmentType->code,
                    'name' => $employmentType->name,
                    'description' => $employmentType->description,
                    'requires_contract_end_date' => $employmentType->requires_contract_end_date,
                    'included_in_payroll' => $employmentType->included_in_payroll,
                    'eligible_for_benefits' => $employmentType->eligible_for_benefits,
                    'eligible_for_overtime' => $employmentType->eligible_for_overtime,
                    'active' => $employmentType->active,
                    'sort_order' => $employmentType->sort_order,
                    'deleted_at' => $employmentType->deleted_at?->toISOString(),
                    'created_at' => $employmentType->created_at?->toISOString(),
                    'updated_at' => $employmentType->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $employmentTypes->currentPage(),
            'last_page' => $employmentTypes->lastPage(),
            'per_page' => $employmentTypes->perPage(),
            'total' => $employmentTypes->total(),
            'from' => $employmentTypes->firstItem(),
            'to' => $employmentTypes->lastItem(),
        ];
    }
}
