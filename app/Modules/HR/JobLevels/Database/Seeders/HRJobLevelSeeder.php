<?php

namespace App\Modules\HR\JobLevels\Database\Seeders;

use App\Modules\HR\JobLevels\Models\JobLevel;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HRJobLevelSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $jobLevels = [
            ['code' => 'L1', 'name' => 'Entry Level', 'description' => 'Level awal untuk peran operasional atau individual contributor junior.', 'sort_order' => 10],
            ['code' => 'L2', 'name' => 'Officer', 'description' => 'Level pelaksana mandiri dengan tanggung jawab area kerja spesifik.', 'sort_order' => 20],
            ['code' => 'L3', 'name' => 'Senior Officer', 'description' => 'Level senior yang menangani pekerjaan kompleks dan mentoring dasar.', 'sort_order' => 30],
            ['code' => 'L4', 'name' => 'Supervisor', 'description' => 'Level pengawas tim kecil, koordinasi harian, dan kontrol kualitas pekerjaan.', 'sort_order' => 40],
            ['code' => 'L5', 'name' => 'Manager', 'description' => 'Level manajerial untuk target unit, resource planning, dan keputusan operasional.', 'sort_order' => 50],
            ['code' => 'L6', 'name' => 'Head of Department', 'description' => 'Level pimpinan departement yang mengelola strategi, policy, dan lintas fungsi.', 'sort_order' => 60],
            ['code' => 'L7', 'name' => 'Director', 'description' => 'Level eksekutif untuk arah bisnis, governance, dan keputusan strategis.', 'sort_order' => 70],
        ];

        foreach ($jobLevels as $item) {
            $jobLevel = JobLevel::withTrashed()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($jobLevel->trashed()) {
                $jobLevel->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'job-levels.view',
            'job-levels.create',
            'job-levels.update',
            'job-levels.delete',
            'job-levels.restore',
            'job-levels.force-delete',
            'job-levels.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'job-levels.view',
            'job-levels.create',
            'job-levels.update',
            'job-levels.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'job-levels.view',
        ]);
    }
}
