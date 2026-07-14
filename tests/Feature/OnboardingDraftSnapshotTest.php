<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OnboardingDraftSnapshotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['onboardings.view', 'onboardings.create'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_authorized_user_creates_draft_with_immutable_task_snapshot_atomically(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.create');
        $owner = User::factory()->create();
        $employee = $this->employee('EMP-001');
        $template = $this->template();

        $this->actingAs($actor)->post(route('hr.onboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => null,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-20',
        ])->assertRedirect(route('hr.onboardings.index'));

        $onboarding = Onboarding::query()->with('tasks')->firstOrFail();
        $this->assertSame('DRAFT', $onboarding->status->value);
        $this->assertSame(['Buat akun', 'Orientasi'], $onboarding->tasks->pluck('title')->all());
        $this->assertSame(['2026-07-18', '2026-07-20'], $onboarding->tasks->pluck('due_date')->map->format('Y-m-d')->all());

        $template->items()->firstOrFail()->update(['title' => 'Judul berubah']);
        $template->delete();

        $this->assertSame(['Buat akun', 'Orientasi'], $onboarding->fresh()->tasks->pluck('title')->all());
    }

    public function test_invalid_employee_contract_or_template_creates_no_partial_rows(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.create');
        $owner = User::factory()->create();
        $employee = $this->employee('EMP-002');
        $otherEmployee = $this->employee('EMP-OTHER');
        $employmentType = EmploymentType::query()->create(['code' => 'PERM', 'name' => 'Permanent']);
        $otherContract = EmployeeContract::query()->create([
            'employee_id' => $otherEmployee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => 'CTR-OTHER',
            'start_date' => '2026-07-01',
            'status' => 'ACTIVE',
        ]);
        $template = $this->template();
        $template->delete();

        $this->actingAs($actor)->post(route('hr.onboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => $otherContract->id,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-20',
        ])->assertSessionHasErrors(['employee_contract_id', 'onboarding_template_id']);

        $this->assertDatabaseCount('hr_onboardings', 0);
        $this->assertDatabaseCount('hr_onboarding_tasks', 0);
    }

    public function test_failure_after_task_creation_rolls_back_entire_aggregate(): void
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('onboardings.create');
        $owner = User::factory()->create();
        $employee = $this->employee('EMP-ROLLBACK');
        $template = $this->template();
        $this->mock(AuditLogService::class, function (MockInterface $mock) {
            $mock->shouldReceive('record')->once()->andThrow(new \RuntimeException('Simulated audit failure'));
        });

        $this->actingAs($actor)->post(route('hr.onboardings.store'), [
            'employee_id' => $employee->id,
            'employee_contract_id' => null,
            'onboarding_template_id' => $template->id,
            'owner_user_id' => $owner->id,
            'start_date' => '2026-07-20',
        ])->assertServerError();

        $this->assertDatabaseCount('hr_onboardings', 0);
        $this->assertDatabaseCount('hr_onboarding_tasks', 0);
    }

    public function test_list_and_create_are_permission_protected(): void
    {
        $viewer = User::factory()->create();
        $viewer->givePermissionTo('onboardings.view');
        $unauthorized = User::factory()->create();

        $this->actingAs($viewer)->get(route('hr.onboardings.index'))->assertOk();
        $this->actingAs($unauthorized)->get(route('hr.onboardings.index'))->assertForbidden();
        $this->actingAs($unauthorized)->post(route('hr.onboardings.store'))->assertForbidden();
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

    private function template(): OnboardingTemplate
    {
        $template = OnboardingTemplate::query()->create(['code' => 'NEW-HIRE', 'name' => 'New Hire', 'active' => true]);
        $template->items()->createMany([
            ['title' => 'Buat akun', 'description' => 'Akun kerja', 'category' => 'IT', 'required' => true, 'due_offset_days' => -2, 'sort_order' => 0],
            ['title' => 'Orientasi', 'description' => null, 'category' => 'HR', 'required' => true, 'due_offset_days' => 0, 'sort_order' => 1],
        ]);

        return $template;
    }
}
