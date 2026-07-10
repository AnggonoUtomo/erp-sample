<?php

namespace App\Modules\HR\HRReferenceData\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\HRReferenceData\DTO\ReferenceCategoryData;
use App\Modules\HR\HRReferenceData\DTO\ReferenceDataData;
use App\Modules\HR\HRReferenceData\Models\ReferenceCategory;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\HRReferenceData\Transactions\HRReferenceDataTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class HRReferenceDataService
{
    private const AUDIT_FIELDS = [
        'category',
        'code',
        'name',
        'description',
        'metadata',
        'active',
        'sort_order',
    ];

    private const CATEGORY_AUDIT_FIELDS = [
        'code',
        'name',
        'description',
        'active',
        'sort_order',
    ];

    public function __construct(
        private readonly HRReferenceDataTransaction $transaction,
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
        $category = $filters['category'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $archive = $filters['archive'] ?? 'active';
        $perPage = $this->resolvePerPage($filters);

        $referenceData = ReferenceData::query()
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('category', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category !== 'all', fn (Builder $query) => $query->where('category', $category))
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'referenceData' => $this->transformPaginator($referenceData),
            'categoryOptions' => $this->categoryOptions(),
            'referenceCategories' => $this->referenceCategories(),
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
                'archive' => $archive,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => ReferenceData::query()->count(),
                'active' => ReferenceData::query()->where('active', true)->count(),
                'inactive' => ReferenceData::query()->where('active', false)->count(),
                'categories' => ReferenceCategory::query()->count(),
                'archived' => ReferenceData::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(ReferenceDataData $data): ReferenceData
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder($data->category);

            $referenceData = ReferenceData::query()->create($payload);

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceData.created',
                auditable: $referenceData,
                description: "Created HR reference data {$referenceData->category}:{$referenceData->code}",
                newValues: $referenceData->only(self::AUDIT_FIELDS),
            );

            return $referenceData->refresh();
        });
    }

    public function update(ReferenceData $referenceData, ReferenceDataData $data): ReferenceData
    {
        return $this->transaction->run(function () use ($referenceData, $data) {
            $oldValues = $referenceData->only(self::AUDIT_FIELDS);

            $referenceData->update($this->payload($data));

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceData.updated',
                auditable: $referenceData,
                description: "Updated HR reference data {$referenceData->category}:{$referenceData->code}",
                oldValues: $oldValues,
                newValues: $referenceData->only(self::AUDIT_FIELDS),
            );

            return $referenceData->refresh();
        });
    }

    public function delete(ReferenceData $referenceData): void
    {
        $this->transaction->run(function () use ($referenceData) {
            $oldValues = $referenceData->only(self::AUDIT_FIELDS);

            $referenceData->delete();

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceData.deleted',
                auditable: $referenceData,
                description: "Archived HR reference data {$oldValues['category']}:{$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(ReferenceData $referenceData): void
    {
        $this->transaction->run(function () use ($referenceData) {
            $oldValues = $referenceData->only(self::AUDIT_FIELDS);

            $referenceData->restore();

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceData.restored',
                auditable: $referenceData,
                description: "Restored HR reference data {$referenceData->category}:{$referenceData->code}",
                oldValues: $oldValues,
                newValues: $referenceData->fresh()?->only(self::AUDIT_FIELDS),
            );
        });
    }

    public function forceDelete(ReferenceData $referenceData): void
    {
        $this->transaction->run(function () use ($referenceData) {
            $oldValues = $referenceData->only(self::AUDIT_FIELDS);

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceData.force-deleted',
                auditable: $referenceData,
                description: "Force deleted HR reference data {$oldValues['category']}:{$oldValues['code']}",
                oldValues: $oldValues,
            );

            $referenceData->forceDelete();
        });
    }

    public function createCategory(ReferenceCategoryData $data): ReferenceCategory
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->categoryPayload($data);
            $payload['sort_order'] ??= $this->nextCategorySortOrder();

            $category = ReferenceCategory::query()->create($payload);

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceCategory.created',
                auditable: $category,
                description: "Created HR reference category {$category->code}",
                newValues: $category->only(self::CATEGORY_AUDIT_FIELDS),
            );

            return $category->refresh();
        });
    }

    public function updateCategory(ReferenceCategory $category, ReferenceCategoryData $data): ReferenceCategory
    {
        return $this->transaction->run(function () use ($category, $data) {
            $oldValues = $category->only(self::CATEGORY_AUDIT_FIELDS);
            $oldCode = $category->code;

            $category->update($this->categoryPayload($data));

            if ($oldCode !== $category->code) {
                ReferenceData::query()->where('category', $oldCode)->update(['category' => $category->code]);
            }

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceCategory.updated',
                auditable: $category,
                description: "Updated HR reference category {$category->code}",
                oldValues: $oldValues,
                newValues: $category->only(self::CATEGORY_AUDIT_FIELDS),
            );

            return $category->refresh();
        });
    }

    public function deleteCategory(ReferenceCategory $category): void
    {
        if (ReferenceData::withTrashed()->where('category', $category->code)->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori sedang dipakai oleh reference data dan tidak bisa dihapus.',
            ]);
        }

        $this->transaction->run(function () use ($category) {
            $oldValues = $category->only(self::CATEGORY_AUDIT_FIELDS);
            $category->delete();

            $this->audit->record(
                module: 'hr.hr-reference-data',
                event: 'HRReferenceCategory.deleted',
                auditable: $category,
                description: "Deleted HR reference category {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return ReferenceCategory::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['code', 'name'])
            ->map(fn (ReferenceCategory $category) => [
                'value' => $category->code,
                'label' => $category->name,
            ])
            ->values()
            ->all();
    }

    private function referenceCategories(): array
    {
        return ReferenceCategory::query()
            ->withCount(['referenceData as items_count'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (ReferenceCategory $category) => [
                'id' => $category->id,
                'code' => $category->code,
                'name' => $category->name,
                'description' => $category->description,
                'active' => $category->active,
                'sort_order' => $category->sort_order,
                'items_count' => $category->items_count,
                'created_at' => $category->created_at?->toISOString(),
                'updated_at' => $category->updated_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(ReferenceDataData $data): array
    {
        $payload = [
            'category' => $data->category,
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'metadata' => $data->metadata,
            'active' => $data->active,
        ];

        if ($data->sortOrder !== null) {
            $payload['sort_order'] = $data->sortOrder;
        }

        return $payload;
    }

    private function categoryPayload(ReferenceCategoryData $data): array
    {
        $payload = [
            'code' => $data->code,
            'name' => $data->name,
            'description' => $data->description,
            'active' => $data->active,
        ];

        if ($data->sortOrder !== null) {
            $payload['sort_order'] = $data->sortOrder;
        }

        return $payload;
    }

    private function nextSortOrder(string $category): int
    {
        return ((int) ReferenceData::query()->where('category', $category)->max('sort_order')) + 1;
    }

    private function nextCategorySortOrder(): int
    {
        return ((int) ReferenceCategory::query()->max('sort_order')) + 10;
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
    private function transformPaginator(LengthAwarePaginator $referenceData): array
    {
        return [
            'data' => collect($referenceData->items())
                ->map(fn (ReferenceData $item) => [
                    'id' => $item->id,
                    'category' => $item->category,
                    'code' => $item->code,
                    'name' => $item->name,
                    'description' => $item->description,
                    'metadata' => $item->metadata,
                    'active' => $item->active,
                    'sort_order' => $item->sort_order,
                    'deleted_at' => $item->deleted_at?->toISOString(),
                    'created_at' => $item->created_at?->toISOString(),
                    'updated_at' => $item->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $referenceData->currentPage(),
            'last_page' => $referenceData->lastPage(),
            'per_page' => $referenceData->perPage(),
            'total' => $referenceData->total(),
            'from' => $referenceData->firstItem(),
            'to' => $referenceData->lastItem(),
        ];
    }
}
