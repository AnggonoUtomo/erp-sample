<?php

namespace App\Modules\HR\Onboardings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OnboardingTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'hr_onboarding_templates';

    protected $fillable = ['code', 'name', 'description', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    /** @return HasMany<OnboardingTemplateItem, OnboardingTemplate> */
    public function items(): HasMany
    {
        return $this->hasMany(OnboardingTemplateItem::class)->orderBy('sort_order');
    }
}
