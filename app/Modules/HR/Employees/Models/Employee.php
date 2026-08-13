<?php

namespace App\Modules\HR\Employees\Models;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Employee extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'hr_employees';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'supervisor_id',
        'departement_id',
        'position_id',
        'job_level_id',
        'work_location_id',
        'employment_status_id',
        'employment_type_id',
        'employee_number',
        'first_name',
        'last_name',
        'display_name',
        'work_email',
        'personal_email',
        'phone',
        'date_of_birth',
        'place_of_birth',
        'national_id',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'hired_at',
        'ended_at',
        'notes',
        'active',
    ];

    protected $appends = [
        'avatar',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hired_at' => 'date',
            'date_of_birth' => 'date',
            'ended_at' => 'date',
            'active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function getAvatarAttribute(): ?string
    {
        return $this->getFirstMediaUrl('avatar') ?: null;
    }

    /**
     * @return BelongsTo<User, Employee>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Employee, Employee> */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supervisor_id');
    }

    /**
     * @return BelongsTo<Departement, Employee>
     */
    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    /**
     * @return BelongsTo<Position, Employee>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    /**
     * @return BelongsTo<JobLevel, Employee>
     */
    public function jobLevel(): BelongsTo
    {
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    /**
     * @return BelongsTo<WorkLocation, Employee>
     */
    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id');
    }

    /**
     * @return BelongsTo<EmploymentStatus, Employee>
     */
    public function employmentStatus(): BelongsTo
    {
        return $this->belongsTo(EmploymentStatus::class, 'employment_status_id');
    }

    /**
     * @return BelongsTo<EmploymentType, Employee>
     */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }
}
