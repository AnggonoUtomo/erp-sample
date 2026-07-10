<?php

namespace App\Modules\HR\Positions\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Positions\DTO\PositionData;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\Positions\Transactions\PositionsTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PositionsService
{
    public function __construct(
        private readonly PositionsTransaction $transaction,
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
        $departement = $filters['departement'] ?? 'all';
        $perPage = $this->resolvePerPage($filters);

        $positions = Position::query()
            ->with('departement:id,code,name')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->when($departement !== 'all', fn (Builder $query) => $query->where('departement_id', (int) $departement))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'positions' => $this->transformPaginator($positions),
            'departementOptions' => $this->departementOptions(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'departement' => $departement,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => Position::query()->count(),
                'active' => Position::query()->where('active', true)->count(),
                'inactive' => Position::query()->where('active', false)->count(),
                'departements' => Position::query()->distinct('departement_id')->count('departement_id'),
            ],
        ];
    }

    public function create(PositionData $data): Position
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $position = Position::query()->create($payload);

            $this->audit->record(
                module: 'hr.positions',
                event: 'Position.created',
                auditable: $position,
                description: "Created Position {$position->code}",
                newValues: $position->only(['departement_id', 'code', 'name', 'description', 'active', 'sort_order']),
            );

            return $position->refresh();
        });
    }

    public function update(Position $position, PositionData $data): Position
    {
        return $this->transaction->run(function () use ($position, $data) {
            $oldValues = $position->only(['departement_id', 'code', 'name', 'description', 'active', 'sort_order']);

            $position->update($this->payload($data));

            $this->audit->record(
                module: 'hr.positions',
                event: 'Position.updated',
                auditable: $position,
                description: "Updated Position {$position->code}",
                oldValues: $oldValues,
                newValues: $position->only(['departement_id', 'code', 'name', 'description', 'active', 'sort_order']),
            );

            return $position->refresh();
        });
    }

    public function delete(Position $position): void
    {
        $this->transaction->run(function () use ($position) {
            $oldValues = $position->only(['departement_id', 'code', 'name', 'description', 'active', 'sort_order']);

            $position->delete();

            $this->audit->record(
                module: 'hr.positions',
                event: 'Position.deleted',
                auditable: $position,
                description: "Deleted Position {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(PositionData $data): array
    {
        $payload = [
            'departement_id' => $data->departementId,
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
        return ((int) Position::query()->max('sort_order')) + 1;
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
     * @return array<int, array{id: int, code: string, name: string}>
     */
    private function departementOptions(): array
    {
        return Departement::query()
            ->select('id', 'code', 'name')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Departement $departement) => [
                'id' => $departement->id,
                'code' => $departement->code,
                'name' => $departement->name,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPaginator(LengthAwarePaginator $positions): array
    {
        return [
            'data' => collect($positions->items())
                ->map(fn (Position $position) => [
                    'id' => $position->id,
                    'departement_id' => $position->departement_id,
                    'code' => $position->code,
                    'name' => $position->name,
                    'description' => $position->description,
                    'active' => $position->active,
                    'sort_order' => $position->sort_order,
                    'departement' => $position->departement ? [
                        'id' => $position->departement->id,
                        'code' => $position->departement->code,
                        'name' => $position->departement->name,
                    ] : null,
                    'created_at' => $position->created_at?->toISOString(),
                    'updated_at' => $position->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $positions->currentPage(),
            'last_page' => $positions->lastPage(),
            'per_page' => $positions->perPage(),
            'total' => $positions->total(),
            'from' => $positions->firstItem(),
            'to' => $positions->lastItem(),
        ];
    }
}
