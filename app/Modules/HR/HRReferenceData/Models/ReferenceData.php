<?php

namespace App\Modules\HR\HRReferenceData\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceData extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_reference_data';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'code',
        'name',
        'description',
        'metadata',
        'active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
