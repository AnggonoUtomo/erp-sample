<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingDraftSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow('2026-07-16 09:00:00');

        foreach (['offboardings.view', 'offboardings.create'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_authorized_user_creates_draft_with_immutable_task_snapshot_without_employment_side_effects(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.create');
        $owner = User::factory()->create();
        $employee = $this->employee('EMP-OFF-001');
        $contract = $this->contract($employee);
        $targetStatus = $this->finalStatus();
        $template = $this->template();
        $employeeBefore = $employee->fresh()->getAttributes();
        $contractBefore = $contract->fresh()->getAttributes();

        $this->actingAs($actor)->post(route('hr.offboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Mengundurkan diri secara baik.',
            'notes' => 'Pastikan knowledge transfer selesai.',
        ])->assertRedirect(route('hr.offboardings.index'));

        $offboarding = Offboarding::query()->with('tasks')->firstOrFail();
        $this->assertSame('DRAFT', $offboarding->status->value);
        $this->assertSame('RESIGNATION', $offboarding->exit_type->value);
        $this->assertSame('Pastikan knowledge transfer selesai.', $offboarding->notes);
        $this->assertSame(['Serah terima aset', 'Exit interview'], $offboarding->tasks->pluck('title')->all());
        $this->assertSame(['2026-07-17', '2026-07-20'], $offboarding->tasks->pluck('due_date')->map->format('Y-m-d')->all());
        $this->assertSame(['IT', 'HR'], $offboarding->tasks->pluck('category')->all());
        $this->assertSame([true, false], $offboarding->tasks->pluck('required')->all());
        $this->assertSame(['asset-custodian', null], $offboarding->tasks->pluck('default_assignee_role')->all());
        $this->assertSame(['PENDING', 'PENDING'], $offboarding->tasks->pluck('status')->map->value->all());

        $template->items()->firstOrFail()->update(['title' => 'Judul template berubah']);
        $template->delete();

        $this->assertSame(['Serah terima aset', 'Exit interview'], $offboarding->fresh()->tasks->pluck('title')->all());
        $this->assertSame($employeeBefore, $employee->fresh()->getAttributes());
        $this->assertSame($contractBefore, $contract->fresh()->getAttributes());
    }

    public function test_invalid_employee_contract_target_status_template_or_owner_creates_no_partial_rows(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.create');
        $owner = User::factory()->create();
        $owner->delete();
        $employee = $this->employee('EMP-OFF-002');
        $otherEmployee = $this->employee('EMP-OFF-OTHER');
        $otherContract = $this->contract($otherEmployee);
        $nonFinalStatus = EmploymentStatus::query()->create([
            'code' => 'ACTIVE',
            'name' => 'Active',
            'is_final_status' => false,
            'active' => true,
        ]);
        $template = $this->template();
        $template->delete();

        $this->actingAs($actor)->post(route('hr.offboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => $otherContract->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $nonFinalStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'INVALID',
            'exit_reason' => 'Uji validasi.',
        ])->assertSessionHasErrors([
            'employee_contract_id',
            'offboarding_template_id',
            'target_employment_status_id',
            'owner_user_id',
            'exit_type',
        ]);

        $this->assertDatabaseCount('hr_offboardings', 0);
        $this->assertDatabaseCount('hr_offboarding_tasks', 0);
    }

    public function test_failure_after_task_creation_rolls_back_entire_aggregate(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.create');
        $owner = User::factory()->create();
        $employee = $this->employee('EMP-OFF-ROLLBACK');
        $targetStatus = $this->finalStatus();
        $template = $this->template();
        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)->post(route('hr.offboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => null,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $targetStatus->id,
            'owner_user_id' => $owner->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'END_OF_CONTRACT',
            'exit_reason' => 'Kontrak selesai.',
        ])->assertServerError();

        $this->assertDatabaseCount('hr_offboardings', 0);
        $this->assertDatabaseCount('hr_offboarding_tasks', 0);
    }

    public function test_list_and_create_are_permission_protected(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('offboardings.view');
        $unauthorized = User::factory()->create();

        $this->actingAs($viewer)->get(route('hr.offboardings.index'))->assertOk();
        $this->actingAs($unauthorized)->get(route('hr.offboardings.index'))->assertForbidden();
        $this->actingAs($unauthorized)->post(route('hr.offboardings.store'))->assertForbidden();
    }

    private function employee(string $number): Employee
    {
        return Employee::query()->create([
            'employee_number' => $number,
            'first_name' => 'Uji',
            'display_name' => "Uji {$number}",
            'active' => true,
        ]);
    }

    private function contract(Employee $employee): EmployeeContract
    {
        $employmentType = EmploymentType::query()->firstOrCreate(
            ['code' => 'PERM'],
            ['name' => 'Permanent'],
        );

        return EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => "CTR-{$employee->employee_number}",
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
    }

    private function finalStatus(): EmploymentStatus
    {
        return EmploymentStatus::query()->create([
            'code' => 'ENDED',
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
    }

    private function template(): OffboardingTemplate
    {
        $template = OffboardingTemplate::query()->create([
            'code' => 'STANDARD-EXIT',
            'name' => 'Standard Exit',
            'active' => true,
        ]);
        $template->items()->createMany([
            [
                'title' => 'Serah terima aset',
                'description' => 'Laptop dan kartu akses',
                'category' => 'IT',
                'required' => true,
                'due_offset_days' => -3,
                'default_assignee_role' => 'asset-custodian',
                'sort_order' => 0,
            ],
            [
                'title' => 'Exit interview',
                'description' => null,
                'category' => 'HR',
                'required' => false,
                'due_offset_days' => 0,
                'default_assignee_role' => null,
                'sort_order' => 1,
            ],
        ]);

        return $template;
    }
}
