<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Database\Seeders\HRUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HREmployeeDocumentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(HRReferenceDataSeeder::class);
        foreach (['employee-documents.view', 'employee-documents.create'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_every_current_employee_document_mutation_is_inventoried_and_requires_authentication(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => str_starts_with((string) $route->getName(), 'hr.employee-documents.'))
            ->filter(fn (Route $route) => array_diff($route->methods(), ['GET', 'HEAD', 'OPTIONS']) !== [])
            ->values();

        $this->assertSame(['hr.employee-documents.store'], $routes->pluck('action.as')->all());
        foreach ($routes as $route) {
            $this->assertContains('auth', $route->gatherMiddleware());
        }
    }

    public function test_guest_and_authenticated_user_without_permission_cannot_create_metadata(): void
    {
        $payload = $this->validPayload('AUTH-DENY-001');

        $this->post(route('hr.employee-documents.store'), $payload)->assertRedirect();
        $this->actingAs(User::factory()->create())
            ->post(route('hr.employee-documents.store'), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('hr_employee_documents', 0);
    }

    public function test_seeded_hr_roles_receive_exact_employee_document_permissions_and_server_enforces_them(): void
    {
        $this->seed(HRUserSeeder::class);
        $expected = [
            'hr-manager' => ['employee-documents.archive', 'employee-documents.attach', 'employee-documents.create', 'employee-documents.manage', 'employee-documents.restore', 'employee-documents.update', 'employee-documents.verify', 'employee-documents.view'],
            'hr-officer' => ['employee-documents.create', 'employee-documents.update', 'employee-documents.view'],
            'hr-viewer' => ['employee-documents.view'],
        ];

        foreach ($expected as $roleName => $permissions) {
            $actual = Role::findByName($roleName)->permissions->pluck('name')
                ->filter(fn (string $permission) => str_starts_with($permission, 'employee-documents.'))
                ->sort()->values()->all();
            $this->assertSame($permissions, $actual, "Permission role {$roleName} tidak sesuai contract.");
        }

        $manager = User::query()->where('email', 'hr.manager@mail.com')->firstOrFail();
        $officer = User::query()->where('email', 'hr.officer@mail.com')->firstOrFail();
        $viewer = User::query()->where('email', 'hr.viewer@mail.com')->firstOrFail();
        $this->actingAs($manager)->post(route('hr.employee-documents.store'), $this->validPayload('AUTH-MANAGER'))->assertRedirect();
        $this->actingAs($officer)->post(route('hr.employee-documents.store'), $this->validPayload('AUTH-OFFICER'))->assertRedirect();
        $this->actingAs($viewer)->get(route('hr.employee-documents.index'))->assertOk();
        $this->actingAs($viewer)->post(route('hr.employee-documents.store'), $this->validPayload('AUTH-VIEWER'))->assertForbidden();
        $this->assertDatabaseCount('hr_employee_documents', 2);
    }

    private function validPayload(string $number): array
    {
        $employee = Employee::query()->first() ?? $this->employee();
        $type = ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'KTP')->firstOrFail();

        return [
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'document_number' => $number,
            'issued_at' => '2026-07-13',
        ];
    }

    private function employee(): Employee
    {
        $status = EmploymentStatus::query()->create(['code' => 'ACTIVE', 'name' => 'Active', 'active' => true]);
        $type = EmploymentType::query()->create(['code' => 'PERM', 'name' => 'Permanent', 'active' => true]);

        return Employee::query()->create([
            'employee_number' => 'EMP-AUTH', 'first_name' => 'Authorization', 'display_name' => 'Authorization Employee',
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);
    }
}
