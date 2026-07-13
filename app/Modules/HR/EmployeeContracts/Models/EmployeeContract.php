<?php

namespace App\Modules\HR\EmployeeContracts\Models;

use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
{
    use SoftDeletes;

    protected $table = 'hr_employee_contracts';

    protected $fillable = ['employee_id', 'employment_type_id', 'contract_number', 'start_date', 'end_date', 'probation_end_date', 'signed_date', 'status', 'ended_reason', 'superseded_by_id', 'notes'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'probation_end_date' => 'date', 'signed_date' => 'date'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_id');
    }

    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeEffectiveOn(Builder $query, string $date): Builder
    {
        return $query->where('status', '!=', 'CANCELLED')
            ->whereDate('start_date', '<=', $date)
            ->where(fn (Builder $query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', $date));
    }

    public function scopeOverlapping(Builder $query, string $startDate, ?string $endDate): Builder
    {
        return $query->where('status', '!=', 'CANCELLED')
            ->whereDate('start_date', '<=', $endDate ?: '9999-12-31')
            ->where(fn (Builder $query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', $startDate));
    }
}
