<?php

namespace App\Modules\Console\NotificationTemplates\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'channel',
        'subject',
        'body',
        'variables',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'active' => 'boolean',
        ];
    }
}
