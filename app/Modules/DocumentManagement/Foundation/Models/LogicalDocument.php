<?php

namespace App\Modules\DocumentManagement\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

class LogicalDocument extends Model
{
    use SoftDeletes;

    protected $table = 'dm_documents';

    protected $fillable = [
        'reference',
        'owner_schema_version',
        'owner_domain',
        'owner_aggregate_type',
        'owner_aggregate_id',
        'current_version_id',
        'status',
        'created_by_reference',
    ];

    protected static function booted(): void
    {
        static::updating(function (LogicalDocument $document): void {
            if ($document->isDirty('reference')) {
                throw new LogicException('A logical document reference is immutable.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'owner_schema_version' => 'integer',
            'current_version_id' => 'integer',
        ];
    }
}
