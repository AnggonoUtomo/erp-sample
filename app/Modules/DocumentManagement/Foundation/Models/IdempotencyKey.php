<?php

namespace App\Modules\DocumentManagement\Foundation\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $table = 'dm_idempotency_keys';

    protected $fillable = [
        'scope',
        'key_hash',
        'request_fingerprint',
        'document_id',
        'version_id',
        'status',
        'expires_at',
    ];

    protected $hidden = ['key_hash', 'request_fingerprint'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }
}
