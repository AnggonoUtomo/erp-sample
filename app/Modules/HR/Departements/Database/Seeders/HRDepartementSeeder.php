<?php

namespace App\Modules\HR\Departements\Database\Seeders;

use App\Modules\HR\Departements\Models\Departement;
use Illuminate\Database\Seeder;

class HRDepartementSeeder extends Seeder
{
    public function run(): void
    {
        $roots = [
            ['code' => 'HRD', 'name' => 'Human Resources', 'description' => 'Mengelola employee lifecycle, policy HR, dan administrasi personalia.', 'sort_order' => 10],
            ['code' => 'OPS', 'name' => 'Operations', 'description' => 'Mengelola kegiatan operasional harian dan koordinasi lintas unit.', 'sort_order' => 20],
            ['code' => 'FIN', 'name' => 'Finance', 'description' => 'Mengelola budgeting, payroll coordination, reimbursement, dan reporting keuangan.', 'sort_order' => 30],
            ['code' => 'IT', 'name' => 'Information Technology', 'description' => 'Mengelola infrastruktur, aplikasi internal, keamanan akses, dan support teknis.', 'sort_order' => 40],
        ];

        foreach ($roots as $root) {
            $departement = Departement::withTrashed()->updateOrCreate(
                ['code' => $root['code']],
                [
                    ...$root,
                    'parent_id' => null,
                    'active' => true,
                ],
            );

            if ($departement->trashed()) {
                $departement->restore();
            }
        }

        $children = [
            ['code' => 'HRD-REC', 'name' => 'Recruitment', 'parent' => 'HRD', 'description' => 'Menangani hiring request, candidate pipeline, interview, dan onboarding awal.', 'sort_order' => 11],
            ['code' => 'HRD-TRN', 'name' => 'Training & Development', 'parent' => 'HRD', 'description' => 'Mengelola pelatihan, competency plan, dan pengembangan karyawan.', 'sort_order' => 12],
            ['code' => 'OPS-FLD', 'name' => 'Field Operations', 'parent' => 'OPS', 'description' => 'Mengelola pelaksanaan operasional lapangan dan resource scheduling.', 'sort_order' => 21],
            ['code' => 'FIN-PAY', 'name' => 'Payroll Administration', 'parent' => 'FIN', 'description' => 'Menyiapkan data payroll, benefit, deduction, dan verifikasi komponen gaji.', 'sort_order' => 31],
            ['code' => 'IT-SUP', 'name' => 'IT Support', 'parent' => 'IT', 'description' => 'Memberikan support perangkat, akun, aplikasi, dan incident teknis internal.', 'sort_order' => 41],
        ];

        foreach ($children as $child) {
            $parent = Departement::withTrashed()->where('code', $child['parent'])->first();

            $departement = Departement::withTrashed()->updateOrCreate(
                ['code' => $child['code']],
                [
                    'name' => $child['name'],
                    'parent_id' => $parent?->id,
                    'description' => $child['description'],
                    'active' => true,
                    'sort_order' => $child['sort_order'],
                ],
            );

            if ($departement->trashed()) {
                $departement->restore();
            }
        }
    }
}
