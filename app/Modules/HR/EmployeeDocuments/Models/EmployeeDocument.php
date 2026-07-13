<?php

namespace App\Modules\HR\EmployeeDocuments\Models;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use SoftDeletes;

    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'employee_id', 'document_type_id', 'document_number', 'document_number_fingerprint',
        'document_number_uniqueness_key', 'issuer', 'issued_at', 'expires_at', 'verification_status',
        'verified_by', 'verified_at', 'verification_reason', 'document_reference',
        'document_reference_version', 'notes',
    ];

    protected $hidden = ['document_number', 'document_number_fingerprint', 'document_number_uniqueness_key'];

    protected function casts(): array
    {
        return [
            'document_number' => 'encrypted', 'issued_at' => 'date', 'expires_at' => 'date',
            'verified_at' => 'datetime', 'document_reference_version' => 'integer',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(ReferenceData::class, 'document_type_id')->withTrashed();
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
