<?php

namespace App\Modules\Console\LoginActivities\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivity extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'event',
        'successful',
        'ip_address',
        'user_agent',
        'device',
        'browser',
        'platform',
        'message',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
