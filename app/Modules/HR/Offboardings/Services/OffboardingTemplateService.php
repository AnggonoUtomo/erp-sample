<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Offboardings\DTO\OffboardingTemplateData;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Transactions\OffboardingTemplateTransaction;

class OffboardingTemplateService
{
    public function __construct(
        private readonly OffboardingTemplateTransaction $transaction,
        private readonly AuditLogService $audit,
    ) {}

    /** @return array{templates: mixed, showArchived: bool} */
    public function getPageData(bool $includeArchived = false): array
    {
        $templates = OffboardingTemplate::query()
            ->when($includeArchived, fn ($query) => $query->withTrashed())
            ->with('items')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (OffboardingTemplate $template) => [
                'id' => $template->id,
                'code' => $template->code,
                'name' => $template->name,
                'description' => $template->description,
                'active' => $template->active,
                'archived' => $template->trashed(),
                'items' => $template->items->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'category' => $item->category,
                    'required' => $item->required,
                    'due_offset_days' => $item->due_offset_days,
                    'default_assignee_role' => $item->default_assignee_role,
                    'sort_order' => $item->sort_order,
                ])->all(),
            ]);

        return [
            'templates' => $templates,
            'showArchived' => $includeArchived,
        ];
    }

    public function create(OffboardingTemplateData $data): OffboardingTemplate
    {
        return $this->transaction->run(function () use ($data) {
            $template = OffboardingTemplate::query()->create([
                'code' => $data->code,
                'name' => $data->name,
                'description' => $data->description,
                'active' => $data->active,
            ]);

            foreach ($data->items as $sortOrder => $item) {
                $template->items()->create([
                    ...$item,
                    'sort_order' => $sortOrder,
                ]);
            }

            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTemplate.created',
                auditable: $template,
                description: "Created offboarding template {$template->code}",
                newValues: [
                    'code' => $template->code,
                    'name' => $template->name,
                    'active' => $template->active,
                    'item_count' => count($data->items),
                ],
            );

            return $template->load('items');
        });
    }

    public function archive(OffboardingTemplate $template): void
    {
        $this->transaction->run(function () use ($template): void {
            $locked = OffboardingTemplate::query()
                ->lockForUpdate()
                ->findOrFail($template->id);

            $locked->delete();
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTemplate.archived',
                auditable: $locked,
                description: "Archived offboarding template {$locked->code}",
                oldValues: [
                    'code' => $locked->code,
                    'active' => $locked->active,
                    'deleted_at' => null,
                ],
                newValues: [
                    'deleted_at' => $locked->deleted_at?->toISOString(),
                ],
            );
        });
    }

    public function restore(OffboardingTemplate $template): void
    {
        $this->transaction->run(function () use ($template): void {
            $locked = OffboardingTemplate::withTrashed()
                ->lockForUpdate()
                ->findOrFail($template->id);

            if (! $locked->trashed()) {
                return;
            }

            $archivedAt = $locked->deleted_at?->toISOString();
            $locked->restore();
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTemplate.restored',
                auditable: $locked,
                description: "Restored offboarding template {$locked->code}",
                oldValues: [
                    'deleted_at' => $archivedAt,
                ],
                newValues: [
                    'code' => $locked->code,
                    'active' => $locked->active,
                    'deleted_at' => null,
                ],
            );
        });
    }
}
