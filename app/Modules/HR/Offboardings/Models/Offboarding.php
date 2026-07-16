<?php

namespace App\Modules\HR\Offboardings\Models;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingExitType;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offboarding extends Model
{
    use SoftDeletes;

    protected $table = 'hr_offboardings';

    protected $hidden = ['active_identity_key', 'request_fingerprint'];

    protected $fillable = [
        'employee_id',
        'employee_contract_id',
        'offboarding_template_id',
        'target_employment_status_id',
        'owner_user_id',
        'exit_date',
        'exit_type',
        'exit_reason',
        'notes',
        'status',
        'active_identity_key',
        'request_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'exit_date' => 'date',
            'exit_type' => OffboardingExitType::class,
            'status' => OffboardingStatus::class,
        ];
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
        return $this->belongsTo(OffboardingTemplate::class, 'offboarding_template_id')->withTrashed();
    }

    public function targetEmploymentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'target_employment_status_id')->withTrashed();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id')->withTrashed();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(OffboardingTask::class)->orderBy('sort_order');
    }
}
