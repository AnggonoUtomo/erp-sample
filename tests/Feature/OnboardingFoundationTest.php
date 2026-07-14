<?php

namespace Tests\Feature;

use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Support\Modules\ModuleContractValidator;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OnboardingFoundationTest extends TestCase
{
    public function test_module_manifest_declares_only_approved_boundaries(): void
    {
        $module = require base_path('app/Modules/HR/Onboardings/module.php');

        $this->assertSame('HR', $module['project']);
        $this->assertSame('Onboardings', $module['name']);
        $this->assertSame(['Employees', 'Console.UserManagements'], $module['dependencies']);
        $this->assertSame(['HR.EmployeeContracts'], $module['integrations']['optional_dependencies']);
        $this->assertTrue($module['exports']['routes']);
        $this->assertTrue($module['exports']['permissions']);
        $this->assertTrue($module['exports']['navigation']);
        $this->assertSame([], app(ModuleContractValidator::class)->validate('HR.Onboardings'));
    }

    public function test_onboarding_status_transition_contract_is_fail_closed(): void
    {
        $this->assertTrue(OnboardingStatus::Draft->canTransitionTo(OnboardingStatus::InProgress));
        $this->assertTrue(OnboardingStatus::Draft->canTransitionTo(OnboardingStatus::Cancelled));
        $this->assertTrue(OnboardingStatus::InProgress->canTransitionTo(OnboardingStatus::Completed));
        $this->assertTrue(OnboardingStatus::InProgress->canTransitionTo(OnboardingStatus::Cancelled));
        $this->assertFalse(OnboardingStatus::Draft->canTransitionTo(OnboardingStatus::Completed));
        $this->assertFalse(OnboardingStatus::Completed->canTransitionTo(OnboardingStatus::Draft));
        $this->assertFalse(OnboardingStatus::Cancelled->canTransitionTo(OnboardingStatus::InProgress));
        $this->assertTrue(OnboardingStatus::Completed->isTerminal());
        $this->assertTrue(OnboardingStatus::Cancelled->isTerminal());
    }

    public function test_onboarding_task_status_transition_contract_is_fail_closed(): void
    {
        $this->assertTrue(OnboardingTaskStatus::Pending->canTransitionTo(OnboardingTaskStatus::InProgress));
        $this->assertTrue(OnboardingTaskStatus::Pending->canTransitionTo(OnboardingTaskStatus::Skipped));
        $this->assertTrue(OnboardingTaskStatus::InProgress->canTransitionTo(OnboardingTaskStatus::Completed));
        $this->assertTrue(OnboardingTaskStatus::Completed->canTransitionTo(OnboardingTaskStatus::Pending));
        $this->assertTrue(OnboardingTaskStatus::Skipped->canTransitionTo(OnboardingTaskStatus::Pending));
        $this->assertFalse(OnboardingTaskStatus::Pending->canTransitionTo(OnboardingTaskStatus::Completed));
        $this->assertFalse(OnboardingTaskStatus::Completed->canTransitionTo(OnboardingTaskStatus::Skipped));
    }

    public function test_runtime_routes_are_limited_to_policy_protected_implemented_slices(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'hr.onboardings.'));

        $this->assertSame([
            'hr.onboardings.index',
            'hr.onboardings.show',
            'hr.onboardings.store',
            'hr.onboardings.templates.archive',
            'hr.onboardings.templates.index',
            'hr.onboardings.templates.restore',
            'hr.onboardings.templates.store',
        ], $routes->pluck('action.as')->sort()->values()->all());

        $routes->each(function ($route) {
            $middleware = $route->gatherMiddleware();
            $this->assertContains('auth', $middleware);
            $this->assertTrue(collect($middleware)->contains(fn (string $item) => str_starts_with($item, 'can:')));
        });
    }
}
