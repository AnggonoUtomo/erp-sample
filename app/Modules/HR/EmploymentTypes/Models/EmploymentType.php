<?php

namespace App\Modules\HR\EmploymentTypes\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploymentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_employment_types';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'requires_contract_end_date',
        'included_in_payroll',
        'eligible_for_benefits',
        'eligible_for_overtime',
        'active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requires_contract_end_date' => 'boolean',
            'included_in_payroll' => 'boolean',
            'eligible_for_benefits' => 'boolean',
            'eligible_for_overtime' => 'boolean',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
