<?php

namespace App\Modules\HR\JobLevels\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\JobLevels\DTO\JobLevelData;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\JobLevels\Transactions\JobLevelsTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class JobLevelsService
{
    public function __construct(
        private readonly JobLevelsTransaction $transaction,
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

        $jobLevels = JobLevel::query()
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
            'jobLevels' => $this->transformPaginator($jobLevels),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'archive' => $archive,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => JobLevel::query()->count(),
                'active' => JobLevel::query()->where('active', true)->count(),
                'inactive' => JobLevel::query()->where('active', false)->count(),
                'archived' => JobLevel::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(JobLevelData $data): JobLevel
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $jobLevel = JobLevel::query()->create($payload);

            $this->audit->record(
                module: 'hr.job-levels',
                event: 'JobLevel.created',
                auditable: $jobLevel,
                description: "Created Job Level {$jobLevel->code}",
                newValues: $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']),
            );

            return $jobLevel->refresh();
        });
    }

    public function update(JobLevel $jobLevel, JobLevelData $data): JobLevel
    {
        return $this->transaction->run(function () use ($jobLevel, $data) {
            $oldValues = $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']);

            $jobLevel->update($this->payload($data));

            $this->audit->record(
                module: 'hr.job-levels',
                event: 'JobLevel.updated',
                auditable: $jobLevel,
                description: "Updated Job Level {$jobLevel->code}",
                oldValues: $oldValues,
                newValues: $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']),
            );

            return $jobLevel->refresh();
        });
    }

    public function delete(JobLevel $jobLevel): void
    {
        $this->transaction->run(function () use ($jobLevel) {
            $oldValues = $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']);

            $jobLevel->delete();

            $this->audit->record(
                module: 'hr.job-levels',
                event: 'JobLevel.deleted',
                auditable: $jobLevel,
                description: "Archived Job Level {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(JobLevel $jobLevel): void
    {
        $this->transaction->run(function () use ($jobLevel) {
            $oldValues = $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']);

            $jobLevel->restore();

            $this->audit->record(
                module: 'hr.job-levels',
                event: 'JobLevel.restored',
                auditable: $jobLevel,
                description: "Restored Job Level {$jobLevel->code}",
                oldValues: $oldValues,
                newValues: $jobLevel->fresh()?->only(['code', 'name', 'description', 'active', 'sort_order']),
            );
        });
    }

    public function forceDelete(JobLevel $jobLevel): void
    {
        $this->transaction->run(function () use ($jobLevel) {
            $oldValues = $jobLevel->only(['code', 'name', 'description', 'active', 'sort_order']);

            $this->audit->record(
                module: 'hr.job-levels',
                event: 'JobLevel.force-deleted',
                auditable: $jobLevel,
                description: "Force deleted Job Level {$oldValues['code']}",
                oldValues: $oldValues,
            );

            $jobLevel->forceDelete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(JobLevelData $data): array
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

    private function nextSortOrder(): int
    {
        return ((int) JobLevel::query()->max('sort_order')) + 1;
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
    private function transformPaginator(LengthAwarePaginator $jobLevels): array
    {
        return [
            'data' => collect($jobLevels->items())
                ->map(fn (JobLevel $jobLevel) => [
                    'id' => $jobLevel->id,
                    'code' => $jobLevel->code,
                    'name' => $jobLevel->name,
                    'description' => $jobLevel->description,
                    'active' => $jobLevel->active,
                    'sort_order' => $jobLevel->sort_order,
                    'deleted_at' => $jobLevel->deleted_at?->toISOString(),
                    'created_at' => $jobLevel->created_at?->toISOString(),
                    'updated_at' => $jobLevel->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $jobLevels->currentPage(),
            'last_page' => $jobLevels->lastPage(),
            'per_page' => $jobLevels->perPage(),
            'total' => $jobLevels->total(),
            'from' => $jobLevels->firstItem(),
            'to' => $jobLevels->lastItem(),
        ];
    }
}
