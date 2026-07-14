<?php

namespace Tests\Feature;

use Illuminate\Routing\Route;
use Tests\TestCase;

class OnboardingAuthorizationMatrixTest extends TestCase
{
    public function test_every_onboarding_mutation_is_inventoried_and_policy_protected(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => str_starts_with((string) $route->getName(), 'hr.onboardings.'))
            ->filter(fn (Route $route) => array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) !== [])
            ->keyBy(fn (Route $route) => (string) $route->getName());

        $expectedPolicies = [
            'hr.onboardings.activate' => 'can:activate,onboarding',
            'hr.onboardings.cancel' => 'can:cancel,onboarding',
            'hr.onboardings.complete' => 'can:complete,onboarding',
            'hr.onboardings.store' => 'can:create,App\Modules\HR\Onboardings\Models\Onboarding',
            'hr.onboardings.tasks.assignment' => 'can:update,task',
            'hr.onboardings.tasks.complete' => 'can:update,task',
            'hr.onboardings.tasks.reopen' => 'can:update,task',
            'hr.onboardings.tasks.skip' => 'can:update,task',
            'hr.onboardings.tasks.start' => 'can:update,task',
            'hr.onboardings.templates.archive' => 'can:delete,template',
            'hr.onboardings.templates.restore' => 'can:restore,template',
            'hr.onboardings.templates.store' => 'can:create,App\Modules\HR\Onboardings\Models\OnboardingTemplate',
        ];

        $this->assertSame(array_keys($expectedPolicies), $routes->keys()->sort()->values()->all());

        foreach ($expectedPolicies as $name => $policy) {
            $middleware = $routes->get($name)->gatherMiddleware();
            $this->assertContains('auth', $middleware, "{$name} wajib memakai auth.");
            $this->assertContains($policy, $middleware, "{$name} wajib memakai {$policy}.");
        }
    }
}
