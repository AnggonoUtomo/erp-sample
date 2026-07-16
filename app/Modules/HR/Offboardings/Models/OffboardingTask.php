<?php

namespace App\Modules\HR\Offboardings\Models;

use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffboardingTask extends Model
{
    protected $table = 'hr_offboarding_tasks';

    protected $fillable = [
        'source_template_item_id',
        'title',
        'description',
        'category',
        'required',
        'due_offset_days',
        'due_date',
        'default_assignee_role',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'due_offset_days' => 'integer',
            'due_date' => 'date',
            'sort_order' => 'integer',
            'status' => OffboardingTaskStatus::class,
        ];
    }

    public function offboarding(): BelongsTo
    {
        return $this->belongsTo(Offboarding::class);
    }
}
