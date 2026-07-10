<?php

namespace App\Modules\HR\Departements\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\Departements\DTO\DepartementData;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Departements\Transactions\DepartementsTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DepartementsService
{
    public function __construct(
        private readonly DepartementsTransaction $transaction,
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
        $perPage = $this->resolvePerPage($filters);

        $departements = Departement::query()
            ->with('parent:id,code,name')
            ->withCount('children')
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
            'departements' => $this->transformPaginator($departements),
            'departementOptions' => $this->departementOptions(),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'summary' => [
                'total' => Departement::query()->count(),
                'active' => Departement::query()->where('active', true)->count(),
                'inactive' => Departement::query()->where('active', false)->count(),
                'root' => Departement::query()->whereNull('parent_id')->count(),
            ],
        ];
    }

    public function create(DepartementData $data): Departement
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder();

            $departement = Departement::query()->create($payload);

            $this->audit->record(
                module: 'hr.departements',
                event: 'Departement.created',
                auditable: $departement,
                description: "Created Departement {$departement->code}",
                newValues: $departement->only(['code', 'name', 'parent_id', 'description', 'active', 'sort_order']),
            );

            return $departement->refresh();
        });
    }

    public function update(Departement $departement, DepartementData $data): Departement
    {
        return $this->transaction->run(function () use ($departement, $data) {
            $oldValues = $departement->only(['code', 'name', 'parent_id', 'description', 'active', 'sort_order']);

            $departement->update($this->payload($data));

            $this->audit->record(
                module: 'hr.departements',
                event: 'Departement.updated',
                auditable: $departement,
                description: "Updated Departement {$departement->code}",
                oldValues: $oldValues,
                newValues: $departement->only(['code', 'name', 'parent_id', 'description', 'active', 'sort_order']),
            );

            return $departement->refresh();
        });
    }

    public function delete(Departement $departement): void
    {
        $this->transaction->run(function () use ($departement) {
            abort_if($departement->children()->exists(), 422, 'Departement masih memiliki child Departement.');

            $oldValues = $departement->only(['code', 'name', 'parent_id', 'description', 'active', 'sort_order']);

            $departement->delete();

            $this->audit->record(
                module: 'hr.departements',
                event: 'Departement.deleted',
                auditable: $departement,
                description: "Deleted Departement {$oldValues['code']}",
                oldValues: $oldValues,
            );
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(DepartementData $data): array
    {
        $payload = [
            'code' => $data->code,
            'name' => $data->name,
            'parent_id' => $data->parentId,
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
        return ((int) Departement::query()->max('sort_order')) + 1;
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
    private function transformPaginator(LengthAwarePaginator $departements): array
    {
        return [
            'data' => collect($departements->items())
                ->map(fn (Departement $departement) => [
                    'id' => $departement->id,
                    'code' => $departement->code,
                    'name' => $departement->name,
                    'description' => $departement->description,
                    'active' => $departement->active,
                    'sort_order' => $departement->sort_order,
                    'children_count' => $departement->children_count,
                    'parent' => $departement->parent ? [
                        'id' => $departement->parent->id,
                        'code' => $departement->parent->code,
                        'name' => $departement->parent->name,
                    ] : null,
                    'created_at' => $departement->created_at?->toISOString(),
                    'updated_at' => $departement->updated_at?->toISOString(),
                ])
                ->values()
                ->all(),
            'current_page' => $departements->currentPage(),
            'last_page' => $departements->lastPage(),
            'per_page' => $departements->perPage(),
            'total' => $departements->total(),
            'from' => $departements->firstItem(),
            'to' => $departements->lastItem(),
        ];
    }
}
