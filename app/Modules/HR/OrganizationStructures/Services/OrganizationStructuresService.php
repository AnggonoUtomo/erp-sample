<?php

namespace App\Modules\HR\OrganizationStructures\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\OrganizationStructures\DTO\OrganizationStructureData;
use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use App\Modules\HR\OrganizationStructures\Transactions\OrganizationStructuresTransaction;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class OrganizationStructuresService
{
    private const AUDIT_FIELDS = [
        'parent_id',
        'departement_id',
        'position_id',
        'code',
        'name',
        'node_type',
        'description',
        'active',
        'sort_order',
    ];

    public function __construct(
        private readonly OrganizationStructuresTransaction $transaction,
        private readonly AuditLogService $audit,
        private readonly SystemSettingService $settings,
    ) {}

    public function getPageData(array $filters = []): array
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $nodeType = $filters['node_type'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $archive = $filters['archive'] ?? 'active';
        $perPage = $this->resolvePerPage($filters);

        $organizationStructures = OrganizationStructure::query()
            ->with(['parent:id,code,name', 'departement:id,code,name', 'position:id,code,name'])
            ->withCount(['children as active_children_count' => fn (Builder $query) => $query->where('active', true)])
            ->when($archive === 'with-trashed', fn (Builder $query) => $query->withTrashed())
            ->when($archive === 'only-trashed', fn (Builder $query) => $query->onlyTrashed())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($nodeType !== 'all', fn (Builder $query) => $query->where('node_type', $nodeType))
            ->when($status !== 'all', fn (Builder $query) => $query->where('active', $status === 'active'))
            ->orderByRaw('parent_id is not null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return [
            'organizationStructures' => $this->transformPaginator($organizationStructures),
            'parentOptions' => $this->parentOptions(),
            'departementOptions' => $this->departementOptions(),
            'positionOptions' => $this->positionOptions(),
            'nodeTypeOptions' => $this->nodeTypeOptions(),
            'filters' => compact('search', 'nodeType', 'status', 'archive') + ['node_type' => $nodeType, 'per_page' => $perPage],
            'summary' => [
                'total' => OrganizationStructure::query()->count(),
                'active' => OrganizationStructure::query()->where('active', true)->count(),
                'root_nodes' => OrganizationStructure::query()->whereNull('parent_id')->count(),
                'position_nodes' => OrganizationStructure::query()->where('node_type', 'position')->count(),
                'archived' => OrganizationStructure::onlyTrashed()->count(),
            ],
        ];
    }

    public function create(OrganizationStructureData $data): OrganizationStructure
    {
        return $this->transaction->run(function () use ($data) {
            $payload = $this->payload($data);
            $payload['sort_order'] ??= $this->nextSortOrder($data->parentId);
            $organizationStructure = OrganizationStructure::query()->create($payload);
            $this->audit->record(module: 'hr.organization-structures', event: 'OrganizationStructure.created', auditable: $organizationStructure, description: "Created organization structure {$organizationStructure->code}", newValues: $organizationStructure->only(self::AUDIT_FIELDS));

            return $organizationStructure->refresh();
        });
    }

    public function update(OrganizationStructure $organizationStructure, OrganizationStructureData $data): OrganizationStructure
    {
        $this->ensureNoCircularParent($organizationStructure, $data->parentId);

        return $this->transaction->run(function () use ($organizationStructure, $data) {
            $oldValues = $organizationStructure->only(self::AUDIT_FIELDS);
            $organizationStructure->update($this->payload($data));
            $this->audit->record(module: 'hr.organization-structures', event: 'OrganizationStructure.updated', auditable: $organizationStructure, description: "Updated organization structure {$organizationStructure->code}", oldValues: $oldValues, newValues: $organizationStructure->only(self::AUDIT_FIELDS));

            return $organizationStructure->refresh();
        });
    }

    public function delete(OrganizationStructure $organizationStructure): void
    {
        if ($organizationStructure->children()->where('active', true)->exists()) {
            throw ValidationException::withMessages(['organization_structure' => 'Structure yang masih memiliki child aktif tidak bisa diarsipkan.']);
        }

        $this->transaction->run(function () use ($organizationStructure) {
            $oldValues = $organizationStructure->only(self::AUDIT_FIELDS);
            $organizationStructure->delete();
            $this->audit->record(module: 'hr.organization-structures', event: 'OrganizationStructure.deleted', auditable: $organizationStructure, description: "Archived organization structure {$oldValues['code']}", oldValues: $oldValues);
        });
    }

    public function restore(OrganizationStructure $organizationStructure): void
    {
        $this->transaction->run(function () use ($organizationStructure) {
            $oldValues = $organizationStructure->only(self::AUDIT_FIELDS);
            $organizationStructure->restore();
            $this->audit->record(module: 'hr.organization-structures', event: 'OrganizationStructure.restored', auditable: $organizationStructure, description: "Restored organization structure {$organizationStructure->code}", oldValues: $oldValues, newValues: $organizationStructure->fresh()?->only(self::AUDIT_FIELDS));
        });
    }

    public function forceDelete(OrganizationStructure $organizationStructure): void
    {
        if ($organizationStructure->children()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['organization_structure' => 'Structure yang masih memiliki child tidak bisa dihapus permanen.']);
        }

        $this->transaction->run(function () use ($organizationStructure) {
            $oldValues = $organizationStructure->only(self::AUDIT_FIELDS);
            $this->audit->record(module: 'hr.organization-structures', event: 'OrganizationStructure.force-deleted', auditable: $organizationStructure, description: "Force deleted organization structure {$oldValues['code']}", oldValues: $oldValues);
            $organizationStructure->forceDelete();
        });
    }

    private function ensureNoCircularParent(OrganizationStructure $organizationStructure, ?int $parentId): void
    {
        while ($parentId !== null) {
            if ($parentId === $organizationStructure->id) {
                throw ValidationException::withMessages(['parent_id' => 'Parent tidak boleh menjadi child dari dirinya sendiri atau turunannya.']);
            }
            $parentId = OrganizationStructure::query()->whereKey($parentId)->value('parent_id');
        }
    }

    private function payload(OrganizationStructureData $data): array
    {
        return [
            'parent_id' => $data->parentId,
            'departement_id' => $data->departementId,
            'position_id' => $data->positionId,
            'code' => $data->code,
            'name' => $data->name,
            'node_type' => $data->nodeType,
            'description' => $data->description,
            'active' => $data->active,
            ...($data->sortOrder !== null ? ['sort_order' => $data->sortOrder] : []),
        ];
    }

    private function nextSortOrder(?int $parentId): int
    {
        return ((int) OrganizationStructure::query()->where('parent_id', $parentId)->max('sort_order')) + 1;
    }

    private function resolvePerPage(array $filters): int
    {
        $settings = $this->settings->paginationSettings();
        $default = (int) $settings['default_per_page'];
        $options = $settings['per_page_options'];
        $perPage = (int) ($filters['per_page'] ?? $default);

        return in_array($perPage, $options, true) ? $perPage : $default;
    }

    private function parentOptions(): array
    {
        return OrganizationStructure::query()->where('active', true)->orderBy('name')->get(['id', 'code', 'name'])->map(fn ($item) => ['value' => $item->id, 'label' => "{$item->name} ({$item->code})"])->all();
    }

    private function departementOptions(): array
    {
        return Departement::query()->where('active', true)->orderBy('name')->get(['id', 'code', 'name'])->map(fn ($item) => ['value' => $item->id, 'label' => "{$item->name} ({$item->code})"])->all();
    }

    private function positionOptions(): array
    {
        return Position::query()->where('active', true)->orderBy('name')->get(['id', 'code', 'name'])->map(fn ($item) => ['value' => $item->id, 'label' => "{$item->name} ({$item->code})"])->all();
    }

    private function nodeTypeOptions(): array
    {
        return collect(['company', 'division', 'departement', 'unit', 'team', 'position'])->map(fn ($type) => ['value' => $type, 'label' => str($type)->replace('-', ' ')->title()->toString()])->all();
    }

    private function transformPaginator(LengthAwarePaginator $organizationStructures): array
    {
        return [
            'data' => collect($organizationStructures->items())->map(fn (OrganizationStructure $item) => [
                'id' => $item->id, 'parent_id' => $item->parent_id, 'departement_id' => $item->departement_id, 'position_id' => $item->position_id, 'code' => $item->code, 'name' => $item->name, 'node_type' => $item->node_type, 'description' => $item->description, 'active' => $item->active, 'sort_order' => $item->sort_order, 'active_children_count' => $item->active_children_count ?? 0,
                'parent' => $item->parent ? ['id' => $item->parent->id, 'code' => $item->parent->code, 'name' => $item->parent->name] : null,
                'departement' => $item->departement ? ['id' => $item->departement->id, 'code' => $item->departement->code, 'name' => $item->departement->name] : null,
                'position' => $item->position ? ['id' => $item->position->id, 'code' => $item->position->code, 'name' => $item->position->name] : null,
                'deleted_at' => $item->deleted_at?->toISOString(), 'created_at' => $item->created_at?->toISOString(), 'updated_at' => $item->updated_at?->toISOString(),
            ])->values()->all(),
            'current_page' => $organizationStructures->currentPage(), 'last_page' => $organizationStructures->lastPage(), 'per_page' => $organizationStructures->perPage(), 'total' => $organizationStructures->total(), 'from' => $organizationStructures->firstItem(), 'to' => $organizationStructures->lastItem(),
        ];
    }
}
