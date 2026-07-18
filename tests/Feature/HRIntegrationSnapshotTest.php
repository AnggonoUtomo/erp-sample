<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeSnapshotV1;
use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HRIntegrationSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_snapshot_provider_returns_operational_identity_snapshot(): void
    {
        Notification::fake();
        Queue::fake();

        $user = User::factory()->create(['email' => 'snapshot.user@example.test']);
        $employee = Employee::query()->create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-SNAPSHOT',
            'first_name' => 'Snapshot',
            'last_name' => 'User',
            'display_name' => 'Snapshot User',
            'work_email' => 'snapshot.employee@example.test',
            'personal_email' => 'private@example.test',
            'phone' => '08123456789',
            'date_of_birth' => '1990-01-01',
            'place_of_birth' => 'Jakarta',
            'national_id' => 'NIK-SHOULD-NOT-LEAK',
            'address' => 'Private address',
            'emergency_contact_name' => 'Emergency Name',
            'emergency_contact_phone' => '0800000000',
            'emergency_contact_relation' => 'Sibling',
            'notes' => 'Internal note',
            'active' => true,
        ]);

        $snapshot = app(EmployeeSnapshotProvider::class)->forEmployee($employee->id);

        $this->assertInstanceOf(EmployeeSnapshotV1::class, $snapshot);
        $this->assertSame([
            'schemaVersion' => 1,
            'employeeId' => $employee->id,
            'employeeNumber' => 'EMP-SNAPSHOT',
            'displayName' => 'Snapshot User',
            'workEmail' => 'snapshot.employee@example.test',
            'isActive' => true,
            'linkedUserId' => $user->id,
        ], $snapshot->toArray());

        $this->assertSame([], app(ForbiddenIntegrationFieldGuard::class)->forbiddenFieldsIn($snapshot->toArray()));
        $this->assertDatabaseHas('hr_employees', [
            'id' => $employee->id,
            'employee_number' => 'EMP-SNAPSHOT',
            'personal_email' => 'private@example.test',
            'national_id' => 'NIK-SHOULD-NOT-LEAK',
            'notes' => 'Internal note',
        ]);
        Notification::assertNothingSent();
        Queue::assertNothingPushed();
    }

    public function test_employee_snapshot_provider_returns_null_for_missing_or_archived_employee(): void
    {
        $employee = Employee::query()->create([
            'employee_number' => 'EMP-ARCHIVED-SNAPSHOT',
            'first_name' => 'Archived',
            'display_name' => 'Archived Snapshot',
            'work_email' => 'archived.snapshot@example.test',
            'active' => true,
        ]);

        $employee->delete();

        $provider = app(EmployeeSnapshotProvider::class);

        $this->assertNull($provider->forEmployee($employee->id));
        $this->assertNull($provider->forEmployee(999999));
    }

    public function test_employee_snapshot_provider_does_not_write_files(): void
    {
        File::spy();

        $employee = Employee::query()->create([
            'employee_number' => 'EMP-NO-FILE',
            'first_name' => 'No',
            'display_name' => 'No File',
            'active' => false,
        ]);

        $snapshot = app(EmployeeSnapshotProvider::class)->forEmployee($employee->id);

        $this->assertFalse($snapshot?->isActive);
        File::shouldNotHaveReceived('put');
        File::shouldNotHaveReceived('append');
        File::shouldNotHaveReceived('delete');
    }
}
