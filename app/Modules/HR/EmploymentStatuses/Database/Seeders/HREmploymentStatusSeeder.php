<?php

namespace App\Modules\HR\EmploymentStatuses\Database\Seeders;

use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HREmploymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $employmentStatuses = [
            [
                'code' => 'PROBATION',
                'name' => 'Probation',
                'description' => 'Karyawan dalam masa percobaan dan masih mengikuti evaluasi awal.',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'sort_order' => 10,
            ],
            [
                'code' => 'PERMANENT',
                'name' => 'Permanent',
                'description' => 'Karyawan tetap dengan hubungan kerja aktif.',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'sort_order' => 20,
            ],
            [
                'code' => 'CONTRACT',
                'name' => 'Contract',
                'description' => 'Karyawan kontrak aktif dengan periode kerja tertentu.',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'sort_order' => 30,
            ],
            [
                'code' => 'INTERN',
                'name' => 'Intern',
                'description' => 'Peserta magang aktif yang dapat memiliki aturan attendance dan payroll berbeda.',
                'requires_attendance' => true,
                'included_in_payroll' => false,
                'is_final_status' => false,
                'sort_order' => 40,
            ],
            [
                'code' => 'SUSPENDED',
                'name' => 'Suspended',
                'description' => 'Status sementara ketika employee tidak aktif bekerja tetapi belum berakhir.',
                'requires_attendance' => false,
                'included_in_payroll' => false,
                'is_final_status' => false,
                'sort_order' => 50,
            ],
            [
                'code' => 'RESIGNED',
                'name' => 'Resigned',
                'description' => 'Employee sudah mengundurkan diri dan tidak aktif untuk attendance/payroll berjalan.',
                'requires_attendance' => false,
                'included_in_payroll' => false,
                'is_final_status' => true,
                'sort_order' => 60,
            ],
            [
                'code' => 'TERMINATED',
                'name' => 'Terminated',
                'description' => 'Hubungan kerja berakhir melalui proses terminasi.',
                'requires_attendance' => false,
                'included_in_payroll' => false,
                'is_final_status' => true,
                'sort_order' => 70,
            ],
        ];

        foreach ($employmentStatuses as $item) {
            $employmentStatus = EmploymentStatus::withTrashed()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'requires_attendance' => $item['requires_attendance'],
                    'included_in_payroll' => $item['included_in_payroll'],
                    'is_final_status' => $item['is_final_status'],
                    'active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($employmentStatus->trashed()) {
                $employmentStatus->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'employment-statuses.view',
            'employment-statuses.create',
            'employment-statuses.update',
            'employment-statuses.delete',
            'employment-statuses.restore',
            'employment-statuses.force-delete',
            'employment-statuses.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'employment-statuses.view',
            'employment-statuses.create',
            'employment-statuses.update',
            'employment-statuses.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'employment-statuses.view',
        ]);
    }
}
