<?php

namespace Tests\Feature;

use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class HRIntegrationContractRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_integration_contracts_module_is_contract_only_without_routes_or_navigation(): void
    {
        $module = ModuleRegistry::modules()
            ->firstWhere('name', 'IntegrationContracts');

        $this->assertNotNull($module);
        $this->assertSame('HR', $module['project']);
        $this->assertSame('integration-contracts', $module['slug']);
        $this->assertSame([
            'routes' => false,
            'permissions' => true,
            'navigation' => false,
        ], $module['exports']);

        $routeNames = collect(Route::getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'hr.integration-contracts.'))
            ->values();

        $this->assertCount(0, $routeNames);
    }

    public function test_hr_integration_contracts_does_not_expose_user_permissions_yet(): void
    {
        $permissions = require app_path('Modules/HR/IntegrationContracts/permissions.php');

        $this->assertSame([], $permissions['permissions']);
        $this->assertSame([], $permissions['roles']['admin']);
        $this->assertSame([], $permissions['roles']['hr-manager']);
        $this->assertSame([], $permissions['roles']['hr-officer']);
        $this->assertSame([], $permissions['roles']['hr-viewer']);
        $this->assertSame([], $permissions['roles']['staff']);
    }

    public function test_registry_lists_snapshot_and_event_contracts_v1(): void
    {
        $registry = app(HRIntegrationContractRegistry::class);

        $this->assertSame([
            'EmployeeSnapshotV1',
            'EmployeeAssignmentSnapshotV1',
            'EmployeeContractSnapshotV1',
            'EmployeeDocumentComplianceSnapshotV1',
        ], $registry->snapshotContracts());

        $this->assertSame([
            'EmployeeCreatedV1',
            'EmployeeProfileUpdatedV1',
            'EmployeeAssignmentChangedV1',
            'EmployeeContractChangedV1',
            'EmployeeDocumentComplianceChangedV1',
            'EmployeeOnboardingActivatedV1',
            'EmployeeOnboardingCompletedV1',
            'EmployeeOffboardingReadyV1',
            'EmployeeOffboardingFinalizedV1',
            'EmploymentTerminatedV1',
        ], $registry->eventContracts());
    }
}
