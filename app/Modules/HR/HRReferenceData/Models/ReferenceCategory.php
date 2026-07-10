<?php

namespace App\Modules\HR\HRReferenceData\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReferenceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_reference_categories';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function referenceData(): HasMany
    {
        return $this->hasMany(ReferenceData::class, 'category', 'code');
    }
}
