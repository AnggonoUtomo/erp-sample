<?php

namespace App\Modules\HR\WorkLocations\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\WorkLocations\Application\DTOs\WorkLocationData;
use App\Modules\HR\WorkLocations\Infrastructure\Transactions\WorkLocationsTransaction;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class WorkLocationsService
{
    private const AUDIT_FIELDS = [
        'code',
        'name',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'timezone',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'description',
        'active',
        'sort_order',
    ];

    public function __construct(
        private readonly WorkLocationsTransaction $transaction,
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
        $city = $filters['city'] ?? 'all';
        $perPage = $this->resolvePerPage($filters);

        $workLocations = WorkLocation::query()
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->when($city !== 'all', fn (Builder $query) => $query->where('city', $city))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'workLocations' => $this->transformPaginator($workLocations),
            'cityOptions' => $this->cityOptions(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'archive' => $archive,
                'city' => $city,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => WorkLocation::query()->count(),
                'active' => WorkLocation::query()->where('active', true)->count(),
                'inactive' => WorkLocation::query()->where('active', false)->count(),
                'archived' => WorkLocation::onlyTrashed()->count(),
            ],
            'mapSettings' => $this->settings->mapRuntimeSettings(),
        ];
    }

    public function create(WorkLocationData $data): WorkLocation
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $workLocation = WorkLocation::query()->create($payload);

            $this->audit->record(
                module: 'hr.work-locations',
                event: 'WorkLocation.created',
                auditable: $workLocation,
                description: "Created Work Location {$workLocation->code}",
                newValues: $workLocation->only(self::AUDIT_FIELDS),
            );

            return $workLocation->refresh();
        });
    }

    public function update(WorkLocation $workLocation, WorkLocationData $data): WorkLocation
    {
        return $this->transaction->run(function () use ($workLocation, $data) {
            $oldValues = $workLocation->only(self::AUDIT_FIELDS);

            $workLocation->update($this->payload($data));

            $this->audit->record(
                module: 'hr.work-locations',
                event: 'WorkLocation.updated',
                auditable: $workLocation,
                description: "Updated Work Location {$workLocation->code}",
                oldValues: $oldValues,
                newValues: $workLocation->only(self::AUDIT_FIELDS),
            );

            return $workLocation->refresh();
        });
    }

    public function delete(WorkLocation $workLocation): void
    {
        $this->transaction->run(function () use ($workLocation) {
            $oldValues = $workLocation->only(self::AUDIT_FIELDS);

            $workLocation->delete();

            $this->audit->record(
                module: 'hr.work-locations',
                event: 'WorkLocation.deleted',
                auditable: $workLocation,
                description: "Archived Work Location {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    public function restore(WorkLocation $workLocation): void
    {
        $this->transaction->run(function () use ($workLocation) {
            $oldValues = $workLocation->only(self::AUDIT_FIELDS);

            $workLocation->restore();

            $this->audit->record(
                module: 'hr.work-locations',
                event: 'WorkLocation.restored',
                auditable: $workLocation,
                description: "Restored Work Location {$workLocation->code}",
                oldValues: $oldValues,
                newValues: $workLocation->fresh()?->only(self::AUDIT_FIELDS),
            );
        });
    }

    public function forceDelete(WorkLocation $workLocation): void
    {
        $this->transaction->run(function () use ($workLocation) {
            $oldValues = $workLocation->only(self::AUDIT_FIELDS);

            $this->audit->record(
                module: 'hr.work-locations',
                event: 'WorkLocation.force-deleted',
                auditable: $workLocation,
                description: "Force deleted Work Location {$oldValues['code']}",
                oldValues: $oldValues,
            );

            $workLocation->forceDelete();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(WorkLocationData $data): array
    {
        $payload = [
            'code' => $data->code,
            'name' => $data->name,
            'address' => $data->address,
            'city' => $data->city,
            'province' => $data->province,
            'country' => $data->country,
            'postal_code' => $data->postalCode,
            'timezone' => $data->timezone,
            'latitude' => $data->latitude,
            'longitude' => $data->longitude,
            'geofence_radius_meters' => $data->geofenceRadiusMeters,
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
        return ((int) WorkLocation::query()->max('sort_order')) + 1;
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
     * @return array<int, string>
     */
    private function cityOptions(): array
    {
        return WorkLocation::query()
            ->select('city')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPaginator(LengthAwarePaginator $workLocations): array
    {
        return [
            'data' => collect($workLocations->items())
                ->map(fn (WorkLocation $workLocation) => [
                    'id' => $workLocation->id,
                    'code' => $workLocation->code,
                    'name' => $workLocation->name,
                    'address' => $workLocation->address,
                    'city' => $workLocation->city,
                    'province' => $workLocation->province,
                    'country' => $workLocation->country,
                    'postal_code' => $workLocation->postal_code,
                    'timezone' => $workLocation->timezone,
                    'latitude' => $workLocation->latitude !== null ? (float) $workLocation->latitude : null,
                    'longitude' => $workLocation->longitude !== null ? (float) $workLocation->longitude : null,
                    'geofence_radius_meters' => $workLocation->geofence_radius_meters,
                    'description' => $workLocation->description,
                    'active' => $workLocation->active,
                    'sort_order' => $workLocation->sort_order,
                    'deleted_at' => $workLocation->deleted_at?->toISOString(),
                    'created_at' => $workLocation->created_at?->toISOString(),
                    'updated_at' => $workLocation->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $workLocations->currentPage(),
            'last_page' => $workLocations->lastPage(),
            'per_page' => $workLocations->perPage(),
            'total' => $workLocations->total(),
            'from' => $workLocations->firstItem(),
            'to' => $workLocations->lastItem(),
        ];
    }
}
