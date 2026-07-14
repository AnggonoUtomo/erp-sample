<?php

namespace App\Modules\HR\Onboardings\Models;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onboarding extends Model
{
    use SoftDeletes;

    protected $table = 'hr_onboardings';

    protected $fillable = ['employee_id', 'employee_contract_id', 'onboarding_template_id', 'owner_user_id', 'start_date', 'status', 'completed_by_user_id', 'completed_at', 'cancelled_by_user_id', 'cancelled_at', 'cancel_reason', 'active_identity_key', 'request_fingerprint'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'status' => OnboardingStatus::class, 'completed_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(EmployeeContract::class, 'employee_contract_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingTemplate::class, 'onboarding_template_id')->withTrashed();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id')->withTrashed();
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by_user_id')->withTrashed();
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id')->withTrashed();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(OnboardingTask::class)->orderBy('sort_order');
    }
}
