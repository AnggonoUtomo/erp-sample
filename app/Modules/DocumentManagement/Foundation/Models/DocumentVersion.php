<?php

namespace App\Modules\DocumentManagement\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class DocumentVersion extends Model
{
    private const IMMUTABLE_INTEGRITY_FIELDS = [
        'document_id',
        'version_number',
        'storage_object_key',
        'original_filename',
        'declared_media_type',
        'detected_media_type',
        'byte_size',
        'sha256',
        'created_by_reference',
    ];

    protected $table = 'dm_document_versions';

    protected $fillable = [
        'document_id', 'version_number', 'storage_object_key', 'original_filename',
        'declared_media_type', 'detected_media_type', 'byte_size', 'sha256',
        'status', 'scan_status', 'created_by_reference',
    ];

    protected $hidden = ['storage_object_key'];

    protected static function booted(): void
    {
        static::updating(function (DocumentVersion $version): void {
            if ($version->getOriginal('status') === 'AVAILABLE'
                && $version->isDirty(self::IMMUTABLE_INTEGRITY_FIELDS)) {
                throw new LogicException('Available document version integrity metadata is immutable.');
            }
        });
    }

    protected function casts(): array
    {
        return ['version_number' => 'integer', 'byte_size' => 'integer'];
    }
}
