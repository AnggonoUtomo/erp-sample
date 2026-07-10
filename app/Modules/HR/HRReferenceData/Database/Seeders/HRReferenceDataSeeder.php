<?php

namespace App\Modules\HR\HRReferenceData\Database\Seeders;

use App\Modules\HR\HRReferenceData\Models\ReferenceCategory;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HRReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $items = [
            ['category' => 'gender', 'code' => 'MALE', 'name' => 'Male', 'sort_order' => 10],
            ['category' => 'gender', 'code' => 'FEMALE', 'name' => 'Female', 'sort_order' => 20],

            ['category' => 'marital-status', 'code' => 'SINGLE', 'name' => 'Single', 'sort_order' => 10],
            ['category' => 'marital-status', 'code' => 'MARRIED', 'name' => 'Married', 'sort_order' => 20],
            ['category' => 'marital-status', 'code' => 'DIVORCED', 'name' => 'Divorced', 'sort_order' => 30],
            ['category' => 'marital-status', 'code' => 'WIDOWED', 'name' => 'Widowed', 'sort_order' => 40],

            ['category' => 'education-level', 'code' => 'SMA', 'name' => 'SMA/SMK', 'sort_order' => 10],
            ['category' => 'education-level', 'code' => 'D3', 'name' => 'Diploma 3', 'sort_order' => 20],
            ['category' => 'education-level', 'code' => 'S1', 'name' => 'Bachelor Degree', 'sort_order' => 30],
            ['category' => 'education-level', 'code' => 'S2', 'name' => 'Master Degree', 'sort_order' => 40],
            ['category' => 'education-level', 'code' => 'S3', 'name' => 'Doctoral Degree', 'sort_order' => 50],

            ['category' => 'religion', 'code' => 'ISLAM', 'name' => 'Islam', 'sort_order' => 10],
            ['category' => 'religion', 'code' => 'CHRISTIAN', 'name' => 'Christian', 'sort_order' => 20],
            ['category' => 'religion', 'code' => 'CATHOLIC', 'name' => 'Catholic', 'sort_order' => 30],
            ['category' => 'religion', 'code' => 'HINDU', 'name' => 'Hindu', 'sort_order' => 40],
            ['category' => 'religion', 'code' => 'BUDDHA', 'name' => 'Buddha', 'sort_order' => 50],
            ['category' => 'religion', 'code' => 'CONFUCIAN', 'name' => 'Confucian', 'sort_order' => 60],

            ['category' => 'blood-type', 'code' => 'A', 'name' => 'A', 'sort_order' => 10],
            ['category' => 'blood-type', 'code' => 'B', 'name' => 'B', 'sort_order' => 20],
            ['category' => 'blood-type', 'code' => 'AB', 'name' => 'AB', 'sort_order' => 30],
            ['category' => 'blood-type', 'code' => 'O', 'name' => 'O', 'sort_order' => 40],

            ['category' => 'bank', 'code' => 'BCA', 'name' => 'Bank Central Asia', 'sort_order' => 10],
            ['category' => 'bank', 'code' => 'MANDIRI', 'name' => 'Bank Mandiri', 'sort_order' => 20],
            ['category' => 'bank', 'code' => 'BRI', 'name' => 'Bank Rakyat Indonesia', 'sort_order' => 30],
            ['category' => 'bank', 'code' => 'BNI', 'name' => 'Bank Negara Indonesia', 'sort_order' => 40],
            ['category' => 'bank', 'code' => 'CIMB', 'name' => 'CIMB Niaga', 'sort_order' => 50],
        ];

        collect($items)
            ->pluck('category')
            ->unique()
            ->values()
            ->each(function (string $category, int $index) {
                $referenceCategory = ReferenceCategory::withTrashed()->updateOrCreate(
                    ['code' => $category],
                    [
                        'name' => str($category)->replace('-', ' ')->title()->toString(),
                        'description' => "Kategori {$category} untuk pilihan master HR.",
                        'active' => true,
                        'sort_order' => ($index + 1) * 10,
                    ],
                );

                if ($referenceCategory->trashed()) {
                    $referenceCategory->restore();
                }
            });

        foreach ($items as $item) {
            $referenceData = ReferenceData::withTrashed()->updateOrCreate(
                [
                    'category' => $item['category'],
                    'code' => $item['code'],
                ],
                [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'metadata' => $item['metadata'] ?? null,
                    'active' => $item['active'] ?? true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($referenceData->trashed()) {
                $referenceData->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'hr-reference-data.view',
            'hr-reference-data.create',
            'hr-reference-data.update',
            'hr-reference-data.delete',
            'hr-reference-data.restore',
            'hr-reference-data.force-delete',
            'hr-reference-data.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'hr-reference-data.view',
            'hr-reference-data.create',
            'hr-reference-data.update',
            'hr-reference-data.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'hr-reference-data.view',
        ]);
    }
}
