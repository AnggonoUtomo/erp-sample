<?php

namespace Tests\Feature;

use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Support\Modules\ModuleContractValidator;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OffboardingFoundationTest extends TestCase
{
    public function test_module_manifest_declares_only_approved_boundaries(): void
    {
        $module = require base_path('app/Modules/HR/Offboardings/module.php');

        $this->assertSame('HR', $module['project']);
        $this->assertSame('Offboardings', $module['name']);
        $this->assertSame([
            'Employees',
            'EmploymentStatuses',
            'Console.UserManagements',
        ], $module['dependencies']);
        $this->assertSame(
            ['HR.EmployeeContracts'],
            $module['integrations']['optional_dependencies'],
        );
        $this->assertTrue($module['exports']['routes']);
        $this->assertTrue($module['exports']['permissions']);
        $this->assertTrue($module['exports']['navigation']);
        $this->assertSame([], $module['events']);
        $this->assertSame([], $module['listeners']);
        $this->assertSame([], app(ModuleContractValidator::class)->validate('HR.Offboardings'));
    }

    public function test_offboarding_status_transition_contract_is_fail_closed(): void
    {
        $this->assertTrue(OffboardingStatus::Draft->canTransitionTo(OffboardingStatus::InProgress));
        $this->assertTrue(OffboardingStatus::Draft->canTransitionTo(OffboardingStatus::Cancelled));
        $this->assertTrue(OffboardingStatus::InProgress->canTransitionTo(OffboardingStatus::ReadyForExit));
        $this->assertTrue(OffboardingStatus::InProgress->canTransitionTo(OffboardingStatus::Cancelled));
        $this->assertTrue(OffboardingStatus::ReadyForExit->canTransitionTo(OffboardingStatus::InProgress));
        $this->assertTrue(OffboardingStatus::ReadyForExit->canTransitionTo(OffboardingStatus::Completed));
        $this->assertTrue(OffboardingStatus::ReadyForExit->canTransitionTo(OffboardingStatus::Cancelled));
        $this->assertFalse(OffboardingStatus::Draft->canTransitionTo(OffboardingStatus::Completed));
        $this->assertFalse(OffboardingStatus::InProgress->canTransitionTo(OffboardingStatus::Completed));
        $this->assertFalse(OffboardingStatus::Completed->canTransitionTo(OffboardingStatus::Draft));
        $this->assertFalse(OffboardingStatus::Cancelled->canTransitionTo(OffboardingStatus::InProgress));
        $this->assertTrue(OffboardingStatus::Completed->isTerminal());
        $this->assertTrue(OffboardingStatus::Cancelled->isTerminal());
    }

    public function test_offboarding_task_status_transition_contract_is_fail_closed(): void
    {
        $this->assertTrue(OffboardingTaskStatus::Pending->canTransitionTo(OffboardingTaskStatus::InProgress));
        $this->assertTrue(OffboardingTaskStatus::Pending->canTransitionTo(OffboardingTaskStatus::Skipped));
        $this->assertTrue(OffboardingTaskStatus::InProgress->canTransitionTo(OffboardingTaskStatus::Completed));
        $this->assertTrue(OffboardingTaskStatus::Completed->canTransitionTo(OffboardingTaskStatus::Pending));
        $this->assertTrue(OffboardingTaskStatus::Skipped->canTransitionTo(OffboardingTaskStatus::Pending));
        $this->assertFalse(OffboardingTaskStatus::Pending->canTransitionTo(OffboardingTaskStatus::Completed));
        $this->assertFalse(OffboardingTaskStatus::Completed->canTransitionTo(OffboardingTaskStatus::Skipped));
    }

    public function test_permissions_follow_least_privilege_role_defaults(): void
    {
        $permissions = require base_path('app/Modules/HR/Offboardings/permissions.php');

        $this->assertContains('offboardings.finalize', $permissions['roles']['admin']);
        $this->assertContains('offboardings.finalize', $permissions['roles']['hr-manager']);
        $this->assertNotContains('offboardings.finalize', $permissions['roles']['hr-officer']);
        $this->assertSame(
            ['hr.view', 'offboardings.view'],
            $permissions['roles']['hr-viewer'],
        );
    }

    public function test_runtime_routes_and_navigation_only_expose_the_policy_backed_template_slice(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'hr.offboardings.'));
        $navigation = require base_path('app/Modules/HR/Offboardings/navigation.php');

        $this->assertSame([
            'hr.offboardings.templates.index',
            'hr.offboardings.templates.store',
        ], $routes->pluck('action.as')->sort()->values()->all());
        $routes->each(function ($route) {
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertTrue(
                collect($route->gatherMiddleware())
                    ->contains(fn (string $middleware) => str_starts_with($middleware, 'can:')),
            );
        });

        $this->assertCount(1, $navigation['items']);
        $this->assertSame('/hr/offboardings/templates', $navigation['items'][0]['url']);
        $this->assertSame(
            ['offboardings.view', 'offboardings.template-manage', 'offboardings.manage'],
            $navigation['items'][0]['permissions'],
        );
    }
}
