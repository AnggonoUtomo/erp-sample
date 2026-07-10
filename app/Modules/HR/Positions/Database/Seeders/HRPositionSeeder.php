<?php

namespace App\Modules\HR\Positions\Database\Seeders;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Positions\Models\Position;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HRPositionSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $positions = [
            ['code' => 'HR-MGR', 'name' => 'HR Manager', 'departement' => 'HRD', 'description' => 'Memimpin strategi HR, policy, employee lifecycle, dan koordinasi people operation.', 'sort_order' => 10],
            ['code' => 'REC-SPEC', 'name' => 'Recruitment Specialist', 'departement' => 'HRD-REC', 'description' => 'Mengelola hiring pipeline, interview coordination, dan candidate experience.', 'sort_order' => 20],
            ['code' => 'TRN-OFC', 'name' => 'Training Officer', 'departement' => 'HRD-TRN', 'description' => 'Mengelola pelatihan, competency matrix, dan program pengembangan karyawan.', 'sort_order' => 30],
            ['code' => 'OPS-SPV', 'name' => 'Operations Supervisor', 'departement' => 'OPS', 'description' => 'Mengawasi operasional harian, shift coordination, dan pelaporan performa lapangan.', 'sort_order' => 40],
            ['code' => 'FIELD-LEAD', 'name' => 'Field Team Lead', 'departement' => 'OPS-FLD', 'description' => 'Memimpin tim lapangan, memastikan kesiapan resource, dan eskalasi issue operasional.', 'sort_order' => 50],
            ['code' => 'PAY-OFC', 'name' => 'Payroll Officer', 'departement' => 'FIN-PAY', 'description' => 'Menyiapkan data payroll, benefit, deduction, dan validasi komponen pembayaran.', 'sort_order' => 60],
            ['code' => 'IT-SUP', 'name' => 'IT Support Specialist', 'departement' => 'IT-SUP', 'description' => 'Memberikan support perangkat, akun, aplikasi, dan incident teknis internal.', 'sort_order' => 70],
        ];

        foreach ($positions as $item) {
            $departement = Departement::query()->where('code', $item['departement'])->first();

            if (! $departement) {
                continue;
            }

            $position = Position::withTrashed()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'departement_id' => $departement->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($position->trashed()) {
                $position->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'positions.view',
            'positions.create',
            'positions.update',
            'positions.delete',
            'positions.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'positions.view',
            'positions.create',
            'positions.update',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'positions.view',
        ]);
    }
}
