<?php

namespace App\Modules\HR\EmploymentTypes\Database\Seeders;

use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HREmploymentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $employmentTypes = [
            [
                'code' => 'PERMANENT',
                'name' => 'Permanent',
                'description' => 'Hubungan kerja tetap untuk employee inti perusahaan.',
                'requires_contract_end_date' => false,
                'included_in_payroll' => true,
                'eligible_for_benefits' => true,
                'eligible_for_overtime' => true,
                'sort_order' => 10,
            ],
            [
                'code' => 'PROBATION',
                'name' => 'Probation',
                'description' => 'Hubungan kerja masa percobaan sebelum menjadi permanent atau kontrak lanjutan.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => true,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => true,
                'sort_order' => 20,
            ],
            [
                'code' => 'CONTRACT',
                'name' => 'Fixed-Term Contract',
                'description' => 'Hubungan kerja kontrak dengan tanggal mulai dan tanggal akhir yang wajib dikontrol.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => true,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => true,
                'sort_order' => 30,
            ],
            [
                'code' => 'INTERN',
                'name' => 'Intern',
                'description' => 'Peserta magang dengan aturan benefit, payroll, dan overtime yang biasanya terbatas.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => false,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => false,
                'sort_order' => 40,
            ],
            [
                'code' => 'OUTSOURCING',
                'name' => 'Outsourcing',
                'description' => 'Tenaga kerja dari vendor pihak ketiga yang tidak selalu masuk payroll internal.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => false,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => false,
                'sort_order' => 50,
            ],
            [
                'code' => 'FREELANCE',
                'name' => 'Freelance',
                'description' => 'Pekerja berbasis proyek atau output dengan skema kompensasi non-payroll rutin.',
                'requires_contract_end_date' => true,
                'included_in_payroll' => false,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => false,
                'sort_order' => 60,
            ],
            [
                'code' => 'PART_TIME',
                'name' => 'Part Time',
                'description' => 'Employee paruh waktu dengan eligibility payroll dan overtime sesuai kebijakan perusahaan.',
                'requires_contract_end_date' => false,
                'included_in_payroll' => true,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => true,
                'sort_order' => 70,
            ],
        ];

        foreach ($employmentTypes as $item) {
            $employmentType = EmploymentType::withTrashed()->updateOrCreate(
                ['code' => $item['code']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'requires_contract_end_date' => $item['requires_contract_end_date'],
                    'included_in_payroll' => $item['included_in_payroll'],
                    'eligible_for_benefits' => $item['eligible_for_benefits'],
                    'eligible_for_overtime' => $item['eligible_for_overtime'],
                    'active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );

            if ($employmentType->trashed()) {
                $employmentType->restore();
            }
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'employment-types.view',
            'employment-types.create',
            'employment-types.update',
            'employment-types.delete',
            'employment-types.restore',
            'employment-types.force-delete',
            'employment-types.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'employment-types.view',
            'employment-types.create',
            'employment-types.update',
            'employment-types.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'employment-types.view',
        ]);
    }
}
