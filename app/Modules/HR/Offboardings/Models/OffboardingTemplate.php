<?php

namespace App\Modules\HR\Offboardings\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OffboardingTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'hr_offboarding_templates';

    protected $fillable = ['code', 'name', 'description', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @param Builder<OffboardingTemplate> $query */
    public function scopeAvailableForOffboarding(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /** @return HasMany<OffboardingTemplateItem, OffboardingTemplate> */
    public function items(): HasMany
    {
        return $this->hasMany(OffboardingTemplateItem::class)->orderBy('sort_order');
    }
}
