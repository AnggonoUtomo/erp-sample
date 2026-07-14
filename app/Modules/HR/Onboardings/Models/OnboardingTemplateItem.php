<?php

namespace App\Modules\HR\Onboardings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTemplateItem extends Model
{
    protected $table = 'hr_onboarding_template_items';

    protected $fillable = ['title', 'description', 'category', 'required', 'due_offset_days', 'default_assignee_role', 'sort_order'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'due_offset_days' => 'integer', 'sort_order' => 'integer'];
    }

    /** @return BelongsTo<OnboardingTemplate, OnboardingTemplateItem> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplate::class, 'onboarding_template_id');
    }
}
