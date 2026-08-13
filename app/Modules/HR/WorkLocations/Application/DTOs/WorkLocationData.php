<?php

namespace App\Modules\HR\WorkLocations\Application\DTOs;

final readonly class WorkLocationData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $address,
        public ?string $city,
        public ?string $province,
        public string $country,
        public ?string $postalCode,
        public string $timezone,
        public ?float $latitude,
        public ?float $longitude,
        public ?int $geofenceRadiusMeters,
        public ?string $description,
        public bool $active,
        public ?int $sortOrder,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: strtoupper((string) $data['code']),
            name: (string) $data['name'],
            address: filled($data['address'] ?? null) ? (string) $data['address'] : null,
            city: filled($data['city'] ?? null) ? (string) $data['city'] : null,
            province: filled($data['province'] ?? null) ? (string) $data['province'] : null,
            country: filled($data['country'] ?? null) ? (string) $data['country'] : 'Indonesia',
            postalCode: filled($data['postal_code'] ?? null) ? (string) $data['postal_code'] : null,
            timezone: filled($data['timezone'] ?? null) ? (string) $data['timezone'] : 'Asia/Jakarta',
            latitude: filled($data['latitude'] ?? null) ? (float) $data['latitude'] : null,
            longitude: filled($data['longitude'] ?? null) ? (float) $data['longitude'] : null,
            geofenceRadiusMeters: filled($data['geofence_radius_meters'] ?? null) ? (int) $data['geofence_radius_meters'] : null,
            description: filled($data['description'] ?? null) ? (string) $data['description'] : null,
            active: (bool) ($data['active'] ?? true),
            sortOrder: filled($data['sort_order'] ?? null) ? (int) $data['sort_order'] : null,
        );
    }
}
