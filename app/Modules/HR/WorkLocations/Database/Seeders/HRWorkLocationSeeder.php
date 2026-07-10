<?php

namespace App\Modules\HR\WorkLocations\Database\Seeders;

use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HRWorkLocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $workLocations = [
            [
                'code' => 'HQ-JKT',
                'name' => 'Head Office Jakarta',
                'address' => 'Jl. Jend. Sudirman Kav. 52-53',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'postal_code' => '12190',
                'timezone' => 'Asia/Jakarta',
                'latitude' => -6.2240000,
                'longitude' => 106.8099000,
                'geofence_radius_meters' => 150,
                'description' => 'Kantor pusat untuk fungsi manajemen, HR, finance, dan operation support.',
                'sort_order' => 10,
            ],
            [
                'code' => 'BR-BDG',
                'name' => 'Bandung Branch',
                'address' => 'Jl. Asia Afrika No. 88',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'country' => 'Indonesia',
                'postal_code' => '40111',
                'timezone' => 'Asia/Jakarta',
                'latitude' => -6.9216000,
                'longitude' => 107.6070000,
                'geofence_radius_meters' => 120,
                'description' => 'Cabang operasional wilayah Bandung dan sekitarnya.',
                'sort_order' => 20,
            ],
            [
                'code' => 'BR-SBY',
                'name' => 'Surabaya Branch',
                'address' => 'Jl. Basuki Rahmat No. 12',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'country' => 'Indonesia',
                'postal_code' => '60271',
                'timezone' => 'Asia/Jakarta',
                'latitude' => -7.2575000,
                'longitude' => 112.7521000,
                'geofence_radius_meters' => 120,
                'description' => 'Cabang operasional wilayah Surabaya dan Jawa Timur.',
                'sort_order' => 30,
            ],
            [
                'code' => 'REMOTE-ID',
                'name' => 'Remote Indonesia',
                'address' => null,
                'city' => 'Remote',
                'province' => null,
                'country' => 'Indonesia',
                'postal_code' => null,
                'timezone' => 'Asia/Jakarta',
                'latitude' => null,
                'longitude' => null,
                'geofence_radius_meters' => null,
                'description' => 'Lokasi kerja remote untuk employee berbasis Indonesia.',
                'sort_order' => 40,
            ],
        ];

        foreach ($workLocations as $item) {
            $workLocation = WorkLocation::withTrashed()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'address' => $item['address'],
                    'city' => $item['city'],
                    'province' => $item['province'],
                    'country' => $item['country'],
                    'postal_code' => $item['postal_code'],
                    'timezone' => $item['timezone'],
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'geofence_radius_meters' => $item['geofence_radius_meters'],
                    'description' => $item['description'],
                    'active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($workLocation->trashed()) {
                $workLocation->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'work-locations.view',
            'work-locations.create',
            'work-locations.update',
            'work-locations.delete',
            'work-locations.restore',
            'work-locations.force-delete',
            'work-locations.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'work-locations.view',
            'work-locations.create',
            'work-locations.update',
            'work-locations.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'work-locations.view',
        ]);
    }
}
