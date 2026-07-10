<?php

namespace App\Modules\Console\SystemSettings\DTO;

final readonly class MapSettingData
{
    public function __construct(
        public bool $enabled,
        public ?string $googleMapsApiKey,
        public ?string $googleMapsMapId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            enabled: (bool) ($data['enabled'] ?? false),
            googleMapsApiKey: filled($data['google_maps_api_key'] ?? null) ? (string) $data['google_maps_api_key'] : null,
            googleMapsMapId: filled($data['google_maps_map_id'] ?? null) ? (string) $data['google_maps_map_id'] : null,
        );
    }
}
