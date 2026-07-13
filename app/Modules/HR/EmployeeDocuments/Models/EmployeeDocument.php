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

    private const MATERIAL_FIELDS = [
        'employee_id', 'document_type_id', 'document_number', 'issuer', 'issued_at', 'expires_at',
        'document_reference', 'document_reference_version',
    ];

    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'employee_id', 'document_type_id', 'document_number', 'document_number_fingerprint',
        'document_number_uniqueness_key', 'issuer', 'issued_at', 'expires_at', 'verification_status',
        'verified_by', 'verified_at', 'verification_reason', 'document_reference',
        'document_reference_version', 'attachment_idempotency_key_hash', 'notes',
    ];

    protected $hidden = [
        'document_number', 'document_number_fingerprint', 'document_number_uniqueness_key',
        'attachment_idempotency_key_hash',
    ];

    protected function casts(): array
    {
        return [
            'document_number' => 'encrypted', 'issued_at' => 'date', 'expires_at' => 'date',
            'verified_at' => 'datetime', 'document_reference_version' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (EmployeeDocument $document): void {
            if (! $document->isDirty(self::MATERIAL_FIELDS)) {
                return;
            }

            $document->verification_status = 'PENDING';
            $document->verified_by = null;
            $document->verified_at = null;
            $document->verification_reason = null;
        });
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
