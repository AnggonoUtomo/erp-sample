<?php

namespace App\Modules\HR\EmployeeContracts\Models;

use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContract extends Model
{
    use SoftDeletes;

    protected $table = 'hr_employee_contracts';

    protected $fillable = ['employee_id', 'employment_type_id', 'contract_number', 'start_date', 'end_date', 'probation_end_date', 'signed_date', 'status', 'notes'];

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
}
