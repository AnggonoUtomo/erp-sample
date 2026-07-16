<?php

namespace App\Modules\HR\Offboardings\Models;

use App\Models\User;
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
        'assignee_user_id',
        'sort_order',
        'status',
        'completed_by_user_id',
        'completed_at',
        'completion_note',
    ];

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'due_offset_days' => 'integer',
            'due_date' => 'date',
            'sort_order' => 'integer',
            'status' => OffboardingTaskStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function offboarding(): BelongsTo
    {
        return $this->belongsTo(Offboarding::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id')->withTrashed();
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id')->withTrashed();
    }
}
