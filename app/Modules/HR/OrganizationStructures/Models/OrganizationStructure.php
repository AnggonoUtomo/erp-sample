<?php

namespace App\Modules\HR\OrganizationStructures\Models;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganizationStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_organization_structures';

    protected $fillable = [
        'parent_id',
        'departement_id',
        'position_id',
        'code',
        'name',
        'node_type',
        'description',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
}
