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

    /** @return array{templates: mixed} */
    public function getPageData(): array
    {
        $templates = OffboardingTemplate::query()
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

        return ['templates' => $templates];
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
}
