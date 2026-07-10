<?php

namespace App\Modules\HR\EmploymentStatuses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_employment_statuses';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'requires_attendance',
        'included_in_payroll',
        'is_final_status',
        'active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_attendance' => 'boolean',
            'included_in_payroll' => 'boolean',
            'is_final_status' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
