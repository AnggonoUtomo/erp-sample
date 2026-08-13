<?php

namespace App\Modules\HR\WorkLocations\Http\Requests;

use App\Modules\HR\WorkLocations\Application\DTOs\WorkLocationData;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', WorkLocation::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', 'alpha_dash:ascii', 'unique:hr_work_locations,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'timezone' => ['required', 'string', 'max:64', Rule::in(timezone_identifiers_list())],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function toDto(): WorkLocationData
    {
        return WorkLocationData::fromArray($this->validated());
    }
}
