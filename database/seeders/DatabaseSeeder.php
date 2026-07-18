<?php

namespace Database\Seeders;

use App\Modules\HR\Departements\Database\Seeders\HRDepartementSeeder;
use App\Modules\HR\Employees\Database\Seeders\HREmployeeSeeder;
use App\Modules\HR\EmploymentStatuses\Database\Seeders\HREmploymentStatusSeeder;
use App\Modules\HR\EmploymentTypes\Database\Seeders\HREmploymentTypeSeeder;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReports\Database\Seeders\HRReportLifecycleSeeder;
use App\Modules\HR\JobLevels\Database\Seeders\HRJobLevelSeeder;
use App\Modules\HR\OrganizationStructures\Database\Seeders\HROrganizationStructureSeeder;
use App\Modules\HR\Positions\Database\Seeders\HRPositionSeeder;
use App\Modules\HR\WorkLocations\Database\Seeders\HRWorkLocationSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ModulePermissionSeeder::class,
            UserSeeder::class,
            HRUserSeeder::class,
            HRDepartementSeeder::class,
            HRJobLevelSeeder::class,
            HRWorkLocationSeeder::class,
            HREmploymentStatusSeeder::class,
            HREmploymentTypeSeeder::class,
            HRReferenceDataSeeder::class,
            HRPositionSeeder::class,
            HROrganizationStructureSeeder::class,
            HREmployeeSeeder::class,
            HRReportLifecycleSeeder::class,
        ]);
    }
}
