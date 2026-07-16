<?php

namespace App\Modules\HR\EmployeeMovements\Models;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeMovement extends Model
{
    use SoftDeletes;

    protected $table = 'hr_employee_movements';

    protected $fillable = [
        'employee_id', 'type', 'effective_date', 'status', 'reason', 'notes',
        'before_values', 'after_values', 'created_by', 'approved_by', 'approved_at',
        'applied_by', 'applied_at', 'cancelled_by', 'cancelled_at', 'cancel_reason',
        'archived_by', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date', 'before_values' => 'array', 'after_values' => 'array',
            'approved_at' => 'datetime', 'applied_at' => 'datetime',
            'cancelled_at' => 'datetime', 'archived_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
