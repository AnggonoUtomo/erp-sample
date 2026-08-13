<?php

namespace App\Modules\HR\HRReports\Database\Seeders;

use App\Models\User;
use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Models\ReferenceCategory;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\Offboardings\Enums\OffboardingExitType;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Models\OffboardingTemplateItem;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Models\OnboardingTemplateItem;
use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HRReportLifecycleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $owner = $this->seedOwner();

            $master = $this->seedMasterData();
            $employees = $this->seedEmployees($master);
            $contracts = $this->seedContracts($employees, $master);

            $this->seedDocuments($employees, $master);
            $this->seedMovement($employees, $master, $owner);
            $this->seedOnboarding($employees['newHire'], $contracts['newHire'], $owner);
            $this->seedOffboarding($employees['leaver'], $contracts['leaver'], $master, $owner);
        });
    }

    private function seedOwner(): User
    {
        /** @var User $user */
        $user = User::withTrashed()->updateOrCreate(
            ['email' => 'hr.report.lifecycle@example.test'],
            [
                'name' => 'HR Report Lifecycle Seeder',
                'password' => Hash::make(Str::random(48)),
            ],
        );

        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }

    /**
     * @return array<string, Model>
     */
    private function seedMasterData(): array
    {
        $hrDepartement = $this->upsert(
            Departement::class,
            ['code' => 'RPT-HR'],
            [
                'name' => 'Report Seed HR',
                'description' => 'Data seed khusus HR Reports.',
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $opsDepartement = $this->upsert(
            Departement::class,
            ['code' => 'RPT-OPS'],
            [
                'name' => 'Report Seed Operations',
                'description' => 'Data seed khusus HR Reports.',
                'active' => true,
                'sort_order' => 9002,
            ],
        );
        $hq = $this->upsert(
            WorkLocation::class,
            ['code' => 'RPT-HQ'],
            [
                'name' => 'Report Seed Head Office',
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'timezone' => 'Asia/Jakarta',
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $remote = $this->upsert(
            WorkLocation::class,
            ['code' => 'RPT-REMOTE'],
            [
                'name' => 'Report Seed Remote',
                'city' => 'Remote',
                'country' => 'Indonesia',
                'timezone' => 'Asia/Jakarta',
                'active' => true,
                'sort_order' => 9002,
            ],
        );
        $activeStatus = $this->upsert(
            EmploymentStatus::class,
            ['code' => 'RPT-ACTIVE'],
            [
                'name' => 'Report Seed Active',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $probationStatus = $this->upsert(
            EmploymentStatus::class,
            ['code' => 'RPT-PROBATION'],
            [
                'name' => 'Report Seed Probation',
                'requires_attendance' => true,
                'included_in_payroll' => true,
                'is_final_status' => false,
                'active' => true,
                'sort_order' => 9002,
            ],
        );
        $resignedStatus = $this->upsert(
            EmploymentStatus::class,
            ['code' => 'RPT-RESIGNED'],
            [
                'name' => 'Report Seed Resigned',
                'requires_attendance' => false,
                'included_in_payroll' => false,
                'is_final_status' => true,
                'active' => true,
                'sort_order' => 9003,
            ],
        );
        $permanentType = $this->upsert(
            EmploymentType::class,
            ['code' => 'RPT-PERMANENT'],
            [
                'name' => 'Report Seed Permanent',
                'requires_contract_end_date' => false,
                'included_in_payroll' => true,
                'eligible_for_benefits' => true,
                'eligible_for_overtime' => true,
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $contractType = $this->upsert(
            EmploymentType::class,
            ['code' => 'RPT-CONTRACT'],
            [
                'name' => 'Report Seed Contract',
                'requires_contract_end_date' => true,
                'included_in_payroll' => true,
                'eligible_for_benefits' => false,
                'eligible_for_overtime' => true,
                'active' => true,
                'sort_order' => 9002,
            ],
        );
        $staffLevel = $this->upsert(
            JobLevel::class,
            ['code' => 'RPT-STAFF'],
            [
                'name' => 'Report Seed Staff',
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $hrPosition = $this->upsert(
            Position::class,
            ['code' => 'RPT-HR-GENERALIST'],
            [
                'departement_id' => $hrDepartement->id,
                'name' => 'Report Seed HR Generalist',
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $opsPosition = $this->upsert(
            Position::class,
            ['code' => 'RPT-OPS-ANALYST'],
            [
                'departement_id' => $opsDepartement->id,
                'name' => 'Report Seed Operations Analyst',
                'active' => true,
                'sort_order' => 9002,
            ],
        );
        $documentCategory = $this->upsert(
            ReferenceCategory::class,
            ['code' => 'employee-document-type'],
            [
                'name' => 'Employee Document Type',
                'description' => 'Kategori dokumen employee.',
                'active' => true,
                'sort_order' => 100,
            ],
        );
        $identityDocumentType = $this->upsert(
            ReferenceData::class,
            ['category' => 'employee-document-type', 'code' => 'RPT-ID-CARD'],
            [
                'name' => 'Report Seed Identity Document',
                'description' => 'Tipe dokumen dummy untuk report expiry.',
                'metadata' => [
                    'requires_expiry' => true,
                    'requires_number' => false,
                    'number_unique_scope' => 'NONE',
                    'seed_namespace' => 'RPT',
                ],
                'active' => true,
                'sort_order' => 9001,
            ],
        );
        $workPermitType = $this->upsert(
            ReferenceData::class,
            ['category' => 'employee-document-type', 'code' => 'RPT-WORK-PERMIT'],
            [
                'name' => 'Report Seed Work Permit',
                'description' => 'Tipe dokumen dummy untuk report expiry.',
                'metadata' => [
                    'requires_expiry' => true,
                    'requires_number' => false,
                    'number_unique_scope' => 'NONE',
                    'seed_namespace' => 'RPT',
                ],
                'active' => true,
                'sort_order' => 9002,
            ],
        );

        return compact(
            'hrDepartement',
            'opsDepartement',
            'hq',
            'remote',
            'activeStatus',
            'probationStatus',
            'resignedStatus',
            'permanentType',
            'contractType',
            'staffLevel',
            'hrPosition',
            'opsPosition',
            'documentCategory',
            'identityDocumentType',
            'workPermitType',
        );
    }

    /**
     * @param  array<string, Model>  $master
     * @return array<string, Employee>
     */
    private function seedEmployees(array $master): array
    {
        /** @var Employee $newHire */
        $newHire = $this->upsert(Employee::class, ['employee_number' => 'EMP-RPT-001'], [
            'departement_id' => $master['hrDepartement']->id,
            'position_id' => $master['hrPosition']->id,
            'job_level_id' => $master['staffLevel']->id,
            'work_location_id' => $master['hq']->id,
            'employment_status_id' => $master['probationStatus']->id,
            'employment_type_id' => $master['contractType']->id,
            'first_name' => 'Rani',
            'last_name' => 'Report Seed',
            'display_name' => 'Rani Report Seed',
            'work_email' => 'employee.rpt.001@example.test',
            'hired_at' => '2026-07-01',
            'ended_at' => null,
            'notes' => 'Seed namespace RPT untuk HR Reports.',
            'active' => true,
        ]);

        /** @var Employee $transferred */
        $transferred = $this->upsert(Employee::class, ['employee_number' => 'EMP-RPT-002'], [
            'departement_id' => $master['opsDepartement']->id,
            'position_id' => $master['opsPosition']->id,
            'job_level_id' => $master['staffLevel']->id,
            'work_location_id' => $master['remote']->id,
            'employment_status_id' => $master['activeStatus']->id,
            'employment_type_id' => $master['permanentType']->id,
            'first_name' => 'Bima',
            'last_name' => 'Report Seed',
            'display_name' => 'Bima Report Seed',
            'work_email' => 'employee.rpt.002@example.test',
            'hired_at' => '2026-01-15',
            'ended_at' => null,
            'notes' => 'Seed namespace RPT untuk HR Reports.',
            'active' => true,
        ]);

        /** @var Employee $leaver */
        $leaver = $this->upsert(Employee::class, ['employee_number' => 'EMP-RPT-003'], [
            'departement_id' => $master['opsDepartement']->id,
            'position_id' => $master['opsPosition']->id,
            'job_level_id' => $master['staffLevel']->id,
            'work_location_id' => $master['hq']->id,
            'employment_status_id' => $master['resignedStatus']->id,
            'employment_type_id' => $master['contractType']->id,
            'first_name' => 'Citra',
            'last_name' => 'Report Seed',
            'display_name' => 'Citra Report Seed',
            'work_email' => 'employee.rpt.003@example.test',
            'hired_at' => '2025-10-01',
            'ended_at' => '2026-07-31',
            'notes' => 'Seed namespace RPT untuk HR Reports.',
            'active' => true,
        ]);

        return compact('newHire', 'transferred', 'leaver');
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, Model>  $master
     * @return array<string, EmployeeContract>
     */
    private function seedContracts(array $employees, array $master): array
    {
        /** @var EmployeeContract $newHire */
        $newHire = $this->upsert(EmployeeContract::class, ['contract_number' => 'RPT-CONTRACT-001'], [
            'employee_id' => $employees['newHire']->id,
            'employment_type_id' => $master['contractType']->id,
            'start_date' => '2026-07-01',
            'end_date' => '2026-08-10',
            'probation_end_date' => '2026-09-30',
            'signed_date' => '2026-06-25',
            'status' => 'ACTIVE',
            'notes' => 'Seed contract, bukan data produksi.',
        ]);

        /** @var EmployeeContract $transferred */
        $transferred = $this->upsert(EmployeeContract::class, ['contract_number' => 'RPT-CONTRACT-002'], [
            'employee_id' => $employees['transferred']->id,
            'employment_type_id' => $master['permanentType']->id,
            'start_date' => '2026-01-15',
            'end_date' => null,
            'probation_end_date' => null,
            'signed_date' => '2026-01-10',
            'status' => 'ACTIVE',
            'notes' => 'Seed contract, bukan data produksi.',
        ]);

        /** @var EmployeeContract $leaver */
        $leaver = $this->upsert(EmployeeContract::class, ['contract_number' => 'RPT-CONTRACT-003'], [
            'employee_id' => $employees['leaver']->id,
            'employment_type_id' => $master['contractType']->id,
            'start_date' => '2025-10-01',
            'end_date' => '2026-07-31',
            'probation_end_date' => null,
            'signed_date' => '2025-09-20',
            'status' => 'ACTIVE',
            'notes' => 'Seed contract, bukan data produksi.',
        ]);

        return compact('newHire', 'transferred', 'leaver');
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, Model>  $master
     */
    private function seedDocuments(array $employees, array $master): void
    {
        $this->upsert(EmployeeDocument::class, [
            'employee_id' => $employees['newHire']->id,
            'document_type_id' => $master['identityDocumentType']->id,
        ], [
            'document_number' => null,
            'issuer' => 'Report Seed Authority',
            'issued_at' => '2026-07-01',
            'expires_at' => '2026-08-05',
            'verification_status' => 'PENDING',
            'document_reference' => null,
            'document_reference_version' => null,
            'notes' => 'Metadata-only seed; tidak membuat file/storage.',
        ]);

        $this->upsert(EmployeeDocument::class, [
            'employee_id' => $employees['transferred']->id,
            'document_type_id' => $master['workPermitType']->id,
        ], [
            'document_number' => null,
            'issuer' => 'Report Seed Authority',
            'issued_at' => '2026-01-15',
            'expires_at' => '2027-01-15',
            'verification_status' => 'VERIFIED',
            'document_reference' => null,
            'document_reference_version' => null,
            'notes' => 'Metadata-only seed; tidak membuat file/storage.',
        ]);
    }

    /**
     * @param  array<string, Employee>  $employees
     * @param  array<string, Model>  $master
     */
    private function seedMovement(array $employees, array $master, User $owner): void
    {
        EmployeeMovement::withTrashed()
            ->where('employee_id', $employees['transferred']->id)
            ->where('type', 'TRANSFER')
            ->where('reason', 'Report seed transfer lifecycle.')
            ->forceDelete();

        $values = [
            'employee_id' => $employees['transferred']->id,
            'type' => 'TRANSFER',
            'effective_date' => '2026-07-01',
            'status' => 'APPLIED',
            'reason' => 'Report seed transfer lifecycle.',
            'notes' => 'Seed movement, bukan data produksi.',
            'before_values' => [
                'departement' => 'Report Seed HR',
                'work_location' => 'Report Seed Head Office',
            ],
            'after_values' => [
                'departement_id' => $master['opsDepartement']->id,
                'departement' => 'Report Seed Operations',
                'work_location_id' => $master['remote']->id,
                'work_location' => 'Report Seed Remote',
            ],
            'created_by' => $owner->id,
            'applied_by' => $owner->id,
            'applied_at' => '2026-07-01 09:00:00',
        ];

        if (Schema::hasColumn('hr_employee_movements', 'approved_by')) {
            $values['approved_by'] = $owner->id;
        }

        if (Schema::hasColumn('hr_employee_movements', 'approved_at')) {
            $values['approved_at'] = '2026-06-30 09:00:00';
        }

        EmployeeMovement::query()->create($this->valuesForTable('hr_employee_movements', $values));
    }

    private function seedOnboarding(Employee $employee, EmployeeContract $contract, User $owner): void
    {
        /** @var OnboardingTemplate $template */
        $template = $this->upsert(OnboardingTemplate::class, ['code' => 'RPT-ONBOARDING'], [
            'name' => 'Report Seed Onboarding',
            'description' => 'Template onboarding dummy untuk HR Reports.',
            'active' => true,
        ]);

        /** @var OnboardingTemplateItem $item */
        $item = $template->items()->updateOrCreate(['sort_order' => 1], [
            'title' => 'Lengkapi data employee seed',
            'description' => 'Task dummy untuk lifecycle report.',
            'category' => 'HR_DATA',
            'required' => true,
            'due_offset_days' => 1,
            'default_assignee_role' => 'HR',
        ]);

        $attributes = Schema::hasColumn('hr_onboardings', 'active_identity_key')
            ? ['active_identity_key' => 'RPT-ONBOARDING-EMP-RPT-001']
            : [
                'employee_id' => $employee->id,
                'onboarding_template_id' => $template->id,
                'start_date' => '2026-07-01',
            ];

        $values = [
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-01',
            'status' => OnboardingStatus::InProgress->value,
        ];

        if (Schema::hasColumn('hr_onboardings', 'request_fingerprint')) {
            $values['request_fingerprint'] = hash('sha256', 'RPT-ONBOARDING-EMP-RPT-001');
        }

        /** @var Onboarding $onboarding */
        $onboarding = $this->upsert(Onboarding::class, $attributes, $values);

        $onboarding->tasks()->updateOrCreate(['sort_order' => 1], $this->valuesForTable('hr_onboarding_tasks', [
            'source_template_item_id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'category' => $item->category,
            'required' => $item->required,
            'due_offset_days' => $item->due_offset_days,
            'due_date' => '2026-07-02',
            'default_assignee_role' => $item->default_assignee_role,
            'assignee_user_id' => $owner->id,
            'status' => OnboardingTaskStatus::InProgress->value,
        ]));
    }

    /**
     * @param  array<string, Model>  $master
     */
    private function seedOffboarding(Employee $employee, EmployeeContract $contract, array $master, User $owner): void
    {
        /** @var OffboardingTemplate $template */
        $template = $this->upsert(OffboardingTemplate::class, ['code' => 'RPT-OFFBOARDING'], [
            'name' => 'Report Seed Offboarding',
            'description' => 'Template offboarding dummy untuk HR Reports.',
            'active' => true,
        ]);

        /** @var OffboardingTemplateItem $item */
        $item = $template->items()->updateOrCreate(['sort_order' => 1], [
            'title' => 'Validasi exit employee seed',
            'description' => 'Task dummy untuk lifecycle report.',
            'category' => 'HR_EXIT',
            'required' => true,
            'due_offset_days' => -1,
            'default_assignee_role' => 'HR',
        ]);

        $attributes = Schema::hasColumn('hr_offboardings', 'active_identity_key')
            ? ['active_identity_key' => 'RPT-OFFBOARDING-EMP-RPT-003']
            : [
                'employee_id' => $employee->id,
                'offboarding_template_id' => $template->id,
                'exit_date' => '2026-07-31',
            ];

        $values = [
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $master['resignedStatus']->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-31',
            'exit_type' => OffboardingExitType::Resignation->value,
            'exit_reason' => 'Report seed resignation lifecycle.',
            'notes' => 'Seed offboarding, bukan data produksi.',
            'status' => OffboardingStatus::Draft->value,
        ];

        if (Schema::hasColumn('hr_offboardings', 'request_fingerprint')) {
            $values['request_fingerprint'] = hash('sha256', 'RPT-OFFBOARDING-EMP-RPT-003');
        }

        /** @var Offboarding $offboarding */
        $offboarding = $this->upsert(Offboarding::class, $attributes, $values);

        $offboarding->tasks()->updateOrCreate(['sort_order' => 1], $this->valuesForTable('hr_offboarding_tasks', [
            'source_template_item_id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'category' => $item->category,
            'required' => $item->required,
            'due_offset_days' => $item->due_offset_days,
            'due_date' => '2026-07-30',
            'default_assignee_role' => $item->default_assignee_role,
            'assignee_user_id' => $owner->id,
            'status' => OffboardingTaskStatus::Pending->value,
        ]));
    }

    /**
     * @param  class-string<Model>  $model
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $values
     */
    private function upsert(string $model, array $attributes, array $values): Model
    {
        $values = $this->valuesForTable((new $model)->getTable(), $values);

        /** @var Model $record */
        $record = $model::withTrashed()->updateOrCreate($attributes, $values);

        if (method_exists($record, 'trashed') && $record->trashed()) {
            $record->restore();
        }

        return $record;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    private function valuesForTable(string $table, array $values): array
    {
        return array_intersect_key($values, array_flip(Schema::getColumnListing($table)));
    }
}
