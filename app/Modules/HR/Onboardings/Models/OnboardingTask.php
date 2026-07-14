<?php

namespace App\Modules\HR\Onboardings\Models;

use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTask extends Model
{
    protected $table = 'hr_onboarding_tasks';

    protected $fillable = ['source_template_item_id', 'title', 'description', 'category', 'required', 'due_offset_days', 'due_date', 'default_assignee_role', 'sort_order', 'status'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'due_offset_days' => 'integer', 'due_date' => 'date', 'sort_order' => 'integer', 'status' => OnboardingTaskStatus::class];
    }

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(Onboarding::class);
    }
}
