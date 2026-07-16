<?php

namespace App\Modules\HR\Offboardings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffboardingTemplateItem extends Model
{
    protected $table = 'hr_offboarding_template_items';

    protected $fillable = [
        'title',
        'description',
        'category',
        'required',
        'due_offset_days',
        'default_assignee_role',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'due_offset_days' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<OffboardingTemplate, OffboardingTemplateItem> */
    public function template(): BelongsTo
    {
        return $this->belongsTo(OffboardingTemplate::class, 'offboarding_template_id');
    }
}
