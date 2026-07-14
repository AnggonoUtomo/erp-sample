<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Onboardings\DTO\OnboardingTemplateData;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Transactions\OnboardingTemplateTransaction;

class OnboardingTemplateService
{
    public function __construct(private readonly OnboardingTemplateTransaction $transaction, private readonly AuditLogService $audit) {}

    /** @return array{templates: array<int, mixed>} */
    public function getPageData(): array
    {
        return ['templates' => OnboardingTemplate::query()->with('items')->orderBy('name')->get()->map(fn (OnboardingTemplate $template) => [
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
        ])->all()];
    }

    public function create(OnboardingTemplateData $data): OnboardingTemplate
    {
        return $this->transaction->run(function () use ($data) {
            $template = OnboardingTemplate::query()->create([
                'code' => $data->code, 'name' => $data->name, 'description' => $data->description, 'active' => $data->active,
            ]);

            foreach ($data->items as $sortOrder => $item) {
                $template->items()->create([...$item, 'sort_order' => $sortOrder]);
            }

            $this->audit->record(
                module: 'hr.onboardings', event: 'OnboardingTemplate.created', auditable: $template,
                description: "Created onboarding template {$template->code}",
                newValues: ['code' => $template->code, 'name' => $template->name, 'active' => $template->active, 'item_count' => count($data->items)],
            );

            return $template->load('items');
        });
    }
}
