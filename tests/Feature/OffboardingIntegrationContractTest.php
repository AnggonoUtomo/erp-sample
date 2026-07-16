<?php

namespace Tests\Feature;

use App\Integration\DTO\IntegrationMessageData;
use App\Models\User;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\Offboardings\Integration\Events\EmployeeOffboardingCompletedV1;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Shared\ValueObjects\ModuleIdentifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class OffboardingIntegrationContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::findOrCreate('offboardings.finalize');
    }

    public function test_completed_event_v1_is_published_as_a_minimal_versioned_contract(): void
    {
        $module = require base_path('app/Modules/HR/Offboardings/module.php');

        $this->assertSame([EmployeeOffboardingCompletedV1::class], $module['events']);
        $this->assertSame([], $module['listeners']);
        $this->assertFileExists(base_path('app/Modules/HR/Offboardings/Integration/Schemas/employee-offboarding-completed-v1.json'));

        $runtimeDependencies = [
            ...$module['dependencies'],
            ...($module['integrations']['optional_dependencies'] ?? []),
        ];

        $this->assertNotContains('Attendance', $runtimeDependencies);
        $this->assertNotContains('Payroll', $runtimeDependencies);
        $this->assertNotContains('Accounting', $runtimeDependencies);
        $this->assertNotContains('DocumentManagement', $runtimeDependencies);
    }

    public function test_finalization_dispatches_completed_event_once_after_success(): void
    {
        Event::fake([EmployeeOffboardingCompletedV1::class]);
        $this->travelTo(now()->setDate(2026, 7, 20)->setTime(10, 15));

        $actor = User::factory()->create();
        $actor->givePermissionTo('offboardings.finalize');
        [$offboarding, $employee, $contract, $finalStatus] = $this->offboarding();

        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();
        $this->actingAs($actor)
            ->patch(route('hr.offboardings.finalize', $offboarding), ['business_date' => '2026-07-20'])
            ->assertRedirect();

        Event::assertDispatchedTimes(EmployeeOffboardingCompletedV1::class, 1);
        Event::assertDispatched(EmployeeOffboardingCompletedV1::class, function (EmployeeOffboardingCompletedV1 $event) use ($actor, $offboarding, $employee, $contract, $finalStatus): bool {
            $message = IntegrationMessageData::fromDomainEvent(
                event: $event,
                source: ModuleIdentifier::parse('HR.Offboardings'),
            );

            $payload = $message->payload;
            $metadata = $message->metadata;

            $this->assertSame("hr-offboarding-completed-v1:{$offboarding->id}:2026-07-20T10:15:00+00:00", $message->eventId);
            $this->assertSame('employee-offboarding-completed-v1', $message->eventName);
            $this->assertSame((string) $offboarding->id, $message->aggregateId);
            $this->assertSame('HR.Offboardings', $message->source->key());
            $this->assertNull($message->target);
            $this->assertSame([
                'schema_version',
                'offboarding_id',
                'employee_id',
                'employee_contract_id',
                'target_employment_status_id',
                'exit_type',
                'effective_date',
                'business_date',
                'finalized_by_user_id',
            ], array_keys($payload));
            $this->assertSame(1, $payload['schema_version']);
            $this->assertSame($offboarding->id, $payload['offboarding_id']);
            $this->assertSame($employee->id, $payload['employee_id']);
            $this->assertSame($contract->id, $payload['employee_contract_id']);
            $this->assertSame($finalStatus->id, $payload['target_employment_status_id']);
            $this->assertSame('RESIGNATION', $payload['exit_type']);
            $this->assertSame('2026-07-20', $payload['effective_date']);
            $this->assertSame('2026-07-20', $payload['business_date']);
            $this->assertSame($actor->id, $payload['finalized_by_user_id']);
            $this->assertSame('sync-after-commit', $metadata['delivery']);
            $this->assertSame('consumer-idempotent-by-event-id', $metadata['retry']);
            $this->assertSame('per-offboarding-finalized-at', $metadata['ordering']);

            foreach (['employee_name', 'employee_number', 'exit_reason', 'notes', 'document_reference'] as $forbiddenKey) {
                $this->assertArrayNotHasKey($forbiddenKey, $payload);
            }

            return true;
        });
    }

    public function test_completed_event_schema_matches_public_payload_contract(): void
    {
        $schema = json_decode((string) file_get_contents(base_path('app/Modules/HR/Offboardings/Integration/Schemas/employee-offboarding-completed-v1.json')), true);

        $this->assertSame('https://json-schema.org/draft/2020-12/schema', $schema['$schema']);
        $this->assertSame('EmployeeOffboardingCompletedV1', $schema['title']);
        $this->assertSame([
            'schema_version',
            'offboarding_id',
            'employee_id',
            'employee_contract_id',
            'target_employment_status_id',
            'exit_type',
            'effective_date',
            'business_date',
            'finalized_by_user_id',
        ], $schema['required']);
        $this->assertFalse($schema['additionalProperties']);
    }

    /** @return array{Offboarding, Employee, EmployeeContract, EmploymentStatus} */
    private function offboarding(): array
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
            'employee_number' => 'EMP-OFF-EVENT',
            'first_name' => 'Event',
            'display_name' => 'Event Employee',
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
            'contract_number' => "EVENT-{$employee->id}",
            'start_date' => '2026-01-01',
            'status' => 'ACTIVE',
        ]);
        $template = OffboardingTemplate::query()->create([
            'code' => 'EVENT-'.uniqid(),
            'name' => 'Event Template',
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
            'exit_reason' => 'Approved effective exit.',
            'status' => 'READY_FOR_EXIT',
            'active_identity_key' => "employee:{$employee->id}",
            'request_fingerprint' => hash('sha256', "event-{$employee->id}"),
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
