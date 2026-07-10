<?php

namespace App\Modules\Console\SystemSettings\Http\Requests;

use App\Modules\Console\SystemSettings\DTO\MapSettingData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMapSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system-settings.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'google_maps_api_key' => ['nullable', 'string', 'max:255'],
            'google_maps_map_id' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function toDto(): MapSettingData
    {
        return MapSettingData::fromArray($this->validated());
    }
}
