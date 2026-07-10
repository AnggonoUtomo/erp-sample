<?php

namespace App\Modules\HR\Employees\Database\Seeders;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Models\WorkLocation;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class HREmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();

        $departement = Departement::query()->where('code', 'HRD')->first() ?? Departement::query()->first();
        $position = Position::query()->where('code', 'HR-MGR')->first() ?? Position::query()->first();
        $jobLevel = JobLevel::query()->where('code', 'L3')->first() ?? JobLevel::query()->first();
        $workLocation = WorkLocation::query()->where('code', 'HQ-JKT')->first() ?? WorkLocation::query()->first();
        $employmentStatus = EmploymentStatus::query()->where('code', 'PERMANENT')->first() ?? EmploymentStatus::query()->first();
        $employmentType = EmploymentType::query()->where('code', 'PERMANENT')->first() ?? EmploymentType::query()->first();
        $hrUser = User::query()->where('email', 'hr.manager@mail.com')->first();

        $employees = [
            [
                'user_id' => $hrUser?->id,
                'employee_number' => 'EMP-0001',
                'first_name' => 'Ayu',
                'last_name' => 'Prameswari',
                'display_name' => 'Ayu Prameswari',
                'work_email' => 'ayu.prameswari@company.test',
                'personal_email' => 'ayu.personal@example.com',
                'phone' => '+628120001001',
                'hired_at' => '2024-01-15',
                'notes' => 'HR manager seed employee untuk awal project HR.',
            ],
            [
                'employee_number' => 'EMP-0002',
                'first_name' => 'Bagas',
                'last_name' => 'Nugroho',
                'display_name' => 'Bagas Nugroho',
                'work_email' => 'bagas.nugroho@company.test',
                'personal_email' => 'bagas.personal@example.com',
                'phone' => '+628120001002',
                'hired_at' => '2024-03-01',
                'notes' => 'Employee dummy untuk validasi directory dan filter.',
            ],
        ];

        foreach ($employees as $item) {
            Employee::withTrashed()->updateOrCreate(
                ['employee_number' => $item['employee_number']],
                [
                    'user_id' => $item['user_id'] ?? null,
                    'departement_id' => $departement?->id,
                    'position_id' => $position?->id,
                    'job_level_id' => $jobLevel?->id,
                    'work_location_id' => $workLocation?->id,
                    'employment_status_id' => $employmentStatus?->id,
                    'employment_type_id' => $employmentType?->id,
                    'first_name' => $item['first_name'],
                    'last_name' => $item['last_name'],
                    'display_name' => $item['display_name'],
                    'work_email' => $item['work_email'],
                    'personal_email' => $item['personal_email'],
                    'phone' => $item['phone'],
                    'hired_at' => $item['hired_at'],
                    'ended_at' => null,
                    'notes' => $item['notes'],
                    'active' => true,
                ],
            );
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'hr.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.restore',
            'employees.force-delete',
            'employees.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('hr-manager')->givePermissionTo($permissions);
        Role::findOrCreate('hr-officer')->givePermissionTo([
            'hr.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.restore',
        ]);
        Role::findOrCreate('hr-viewer')->givePermissionTo([
            'hr.view',
            'employees.view',
        ]);
    }
}
