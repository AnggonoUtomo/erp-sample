<?php

namespace App\Modules\DocumentManagement\Foundation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryToken extends Model
{
    protected $table = 'dm_delivery_tokens';

    protected $fillable = [
        'token_hash', 'document_id', 'version_id', 'owner_fingerprint',
        'actor_reference', 'action', 'expires_at', 'used_at', 'revoked_at',
        'revoked_by_reference',
    ];

    protected $hidden = ['token_hash', 'owner_fingerprint'];

    /** @return BelongsTo<LogicalDocument, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(LogicalDocument::class, 'document_id');
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'used_at' => 'immutable_datetime',
            'revoked_at' => 'immutable_datetime',
        ];
    }
}
