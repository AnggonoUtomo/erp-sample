<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HRReportAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach (['hr.view', 'hr-reports.view', 'hr-reports.export', 'hr-reports.manage'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_hr_reports_module_exposes_only_read_only_permissions(): void
    {
        $permissions = require app_path('Modules/HR/HRReports/permissions.php');

        $this->assertSame([
            'hr.view',
            'hr-reports.view',
            'hr-reports.export',
            'hr-reports.manage',
        ], $permissions['permissions']);
    }

    public function test_hr_reports_routes_are_get_only(): void
    {
        $routeNames = collect(Route::getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'hr.reports.'))
            ->mapWithKeys(fn ($route) => [$route->getName() => array_values(array_diff($route->methods(), ['HEAD']))]);

        $this->assertSame([
            'hr.reports.index' => ['GET'],
        ], $routeNames->all());
    }

    public function test_guest_is_redirected_from_hr_reports(): void
    {
        $this->get(route('hr.reports.index'))
            ->assertRedirect(route('hr.login'));
    }

    public function test_authenticated_user_without_permission_cannot_view_hr_reports(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('hr.reports.index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_view_hr_reports_placeholder(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('hr.view', 'hr-reports.view');

        $this->actingAs($user)
            ->get(route('hr.reports.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('hr/hr-reports/index')
                ->where('meta.status', 'read-only-boundary')
            );
    }
}
