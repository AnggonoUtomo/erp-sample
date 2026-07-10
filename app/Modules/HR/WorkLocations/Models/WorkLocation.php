<?php

namespace App\Modules\HR\WorkLocations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hr_work_locations';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'province',
        'country',
        'postal_code',
        'timezone',
        'latitude',
        'longitude',
        'geofence_radius_meters',
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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius_meters' => 'integer',
        ];
    }
}
