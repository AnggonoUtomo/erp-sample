<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingAuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC_REJECTION = 'Finalisasi offboarding ditolak. Periksa kembali kesiapan dan data employment.';

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['offboardings.view', 'offboardings.create', 'offboardings.task-update', 'offboardings.finalize'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_every_offboarding_mutation_is_inventoried_and_policy_protected(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => str_starts_with((string) $route->getName(), 'hr.offboardings.'))
            ->filter(fn (Route $route) => array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) !== [])
            ->keyBy(fn (Route $route) => (string) $route->getName());

        $expectedPolicies = [
            'hr.offboardings.activate' => 'can:activate,offboarding',
            'hr.offboardings.cancel' => 'can:cancel,offboarding',
            'hr.offboardings.finalize' => 'can:finalize,offboarding',
            'hr.offboardings.mark-ready' => 'can:markReady,offboarding',
            'hr.offboardings.store' => 'can:create,App\Modules\HR\Offboardings\Models\Offboarding',
            'hr.offboardings.tasks.assignment' => 'can:update,task',
            'hr.offboardings.tasks.complete' => 'can:update,task',
            'hr.offboardings.tasks.reopen' => 'can:update,task',
            'hr.offboardings.tasks.skip' => 'can:update,task',
            'hr.offboardings.tasks.start' => 'can:update,task',
            'hr.offboardings.templates.archive' => 'can:delete,template',
            'hr.offboardings.templates.restore' => 'can:restore,template',
            'hr.offboardings.templates.store' => 'can:create,App\Modules\HR\Offboardings\Models\OffboardingTemplate',
        ];

        $this->assertSame(array_keys($expectedPolicies), $routes->keys()->sort()->values()->all());
        foreach ($expectedPolicies as $name => $policy) {
            $middleware = $routes->get($name)->gatherMiddleware();
            $this->assertContains('auth', $middleware, "{$name} wajib memakai auth.");
            $this->assertContains($policy, $middleware, "{$name} wajib memakai {$policy}.");
        }
    }

    public function test_guest_viewer_and_officer_without_finalize_permission_are_denied(): void
    {
        [$offboarding, $employee, $contract] = $this->offboarding('EMP-OFF-AUTH-DENIED');

        $this->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect(route('hr.login'));

        foreach ([
            ['offboardings.view'],
            ['offboardings.view', 'offboardings.create', 'offboardings.task-update'],
        ] as $permissions) {
            $user = User::factory()->create();
            $user->givePermissionTo($permissions);
            $this->actingAs($user)
                ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
                ->assertForbidden();
        }

        $this->assertTrue($employee->fresh()->active);
        $this->assertSame('ACTIVE', $contract->fresh()->status);
        $this->assertSame('READY_FOR_EXIT', $offboarding->fresh()->status->value);
    }

    public function test_archived_stale_contract_stale_employee_and_invalid_final_status_fail_closed_generically(): void
    {
        $actor = $this->actor();

        foreach (['archived', 'stale_contract', 'stale_employee', 'invalid_final_status'] as $case) {
            [$offboarding, $employee, $contract, $finalStatus] = $this->offboarding('EMP-OFF-AUTH-'.strtoupper($case));

            match ($case) {
                'archived' => $offboarding->delete(),
                'stale_contract' => $contract->update(['status' => 'ENDED']),
                'stale_employee' => $employee->update(['active' => false]),
                'invalid_final_status' => $finalStatus->update(['active' => false]),
            };

            $response = $this->actingAs($actor)
                ->from(route('hr.offboardings.show', ['offboarding' => $offboarding, 'business_date' => '2026-07-20']))
                ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20']);

            $response->assertRedirect()->assertSessionHasErrors([
                'status' => self::GENERIC_REJECTION,
            ]);
            $errors = session('errors')->getBag('default')->all();
            $serialized = implode(' ', $errors);
            $this->assertStringNotContainsString($employee->display_name, $serialized);
            $this->assertStringNotContainsString($contract->contract_number, $serialized);
            $this->assertStringNotContainsString('lock', strtolower($serialized));
            $this->assertStringNotContainsString('hr_', strtolower($serialized));
        }
    }

    public function test_unknown_id_and_payload_idor_cannot_select_another_employee_contract_or_status(): void
    {
        $actor = $this->actor();
        [$offboarding, $employee, $contract, $finalStatus] = $this->offboarding('EMP-OFF-AUTH-IDOR');
        [$victimOffboarding, $victimEmployee, $victimContract, $victimFinalStatus] = $this->offboarding('EMP-OFF-AUTH-VICTIM');

        $this->actingAs($actor)
            ->patch('/hr/offboardings/999999/finalize', ['business_date' => '2026-07-20'])
            ->assertNotFound();

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), [
                'business_date' => '2026-07-20',
                'employee_id' => $victimEmployee->id,
                'employee_contract_id' => $victimContract->id,
                'target_employment_status_id' => $victimFinalStatus->id,
                'effective_date' => '2026-01-01',
                'reason' => 'Attacker controlled reason.',
                'actor_user_id' => User::factory()->create()->id,
            ])
            ->assertRedirect();

        $this->assertFalse($employee->fresh()->active);
        $this->assertSame($finalStatus->id, $employee->fresh()->employment_status_id);
        $this->assertSame($offboarding->exit_reason, $contract->fresh()->ended_reason);
        $this->assertTrue($victimEmployee->fresh()->active);
        $this->assertSame('ACTIVE', $victimContract->fresh()->status);
        $this->assertSame('READY_FOR_EXIT', $victimOffboarding->fresh()->status->value);
    }

    private function actor(): User
    {
        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.finalize');

        return $actor;
    }

    /** @return array{Offboarding, Employee, EmployeeContract, EmploymentStatus} */
    private function offboarding(string $employeeNumber): array
    {
        $activeStatus = EmploymentStatus::query()->create([
            'code' => 'ACTIVE-'.uniqid(),
            'name' => 'Active',
            'is_final_status' => false,
            'active' => true,
        ]);
        $finalStatus = EmploymentStatus::query()->create([
            'code' => 'ENDED-'.uniqid(),
            'name' => 'Ended',
            'is_final_status' => true,
            'active' => true,
        ]);
        $employee = Employee::query()->create([
            'employment_status_id' => $activeStatus->id,
            'employee_number' => $employeeNumber,
            'first_name' => 'Authorization',
            'display_name' => "Authorization {$employeeNumber}",
            'hired_at' => '2026-01-01',
            'active' => true,
        ]);
        $employmentType = EmploymentType::query()->create([
            'code' => 'PERM-'.uniqid(),
            'name' => 'Permanent',
        ]);
        $contract = EmployeeContract::query()->create([
            'employee_id' => $employee->id,
            'employment_type_id' => $employmentType->id,
            'contract_number' => "AUTH-{$employee->id}",
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => 'AUTH-'.uniqid(),
            'name' => 'Authorization',
            'active' => true,
        ]);
        $offboarding = Offboarding::query()->create([
            'employee_id' => $employee->id,
            'employee_contract_id' => $contract->id,
            'offboarding_template_id' => $template->id,
            'target_employment_status_id' => $finalStatus->id,
            'owner_user_id' => User::factory()->create()->id,
            'exit_date' => '2026-07-20',
            'exit_type' => 'RESIGNATION',
            'exit_reason' => 'Approved authorization test exit.',
            'status' => 'READY_FOR_EXIT',
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "auth-{$employee->id}"),
        ]);
        $offboarding->tasks()->create([
            'title' => 'Required complete',
            'category' => 'HR',
            'required' => true,
            'due_offset_days' => 0,
            'due_date' => '2026-07-20',
            'sort_order' => 0,
            'status' => 'COMPLETED',
        ]);

        return [$offboarding, $employee, $contract, $finalStatus];
    }
}
