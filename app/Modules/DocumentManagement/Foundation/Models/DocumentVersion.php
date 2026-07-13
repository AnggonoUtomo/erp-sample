<?php

namespace App\Modules\DocumentManagement\Foundation\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVersion extends Model
{
    protected $table = 'dm_document_versions';

    protected $fillable = [
        'document_id', 'version_number', 'storage_object_key', 'original_filename',
        'declared_media_type', 'detected_media_type', 'byte_size', 'sha256',
        'status', 'scan_status', 'created_by_reference',
    ];

    protected $hidden = ['storage_object_key'];

    protected function casts(): array
    {
        return ['version_number' => 'integer', 'byte_size' => 'integer'];
    }
}
