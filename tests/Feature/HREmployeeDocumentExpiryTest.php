<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentExpiryService;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeDocumentExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Permission::findOrCreate('employee-documents.view');
        $this->seed(HRReferenceDataSeeder::class);
    }

    public function test_expiry_state_is_correct_on_every_boundary(): void
    {
        $expiry = app(EmployeeDocumentExpiryService::class);
        $asOf = CarbonImmutable::parse('2026-07-13');

        $this->assertSame('NOT_APPLICABLE', $expiry->state(null, $asOf, 30));
        $this->assertSame('EXPIRED', $expiry->state(CarbonImmutable::parse('2026-07-12'), $asOf, 30));
        $this->assertSame('EXPIRING', $expiry->state(CarbonImmutable::parse('2026-07-13'), $asOf, 30));
        $this->assertSame('EXPIRING', $expiry->state(CarbonImmutable::parse('2026-08-12'), $asOf, 30));
        $this->assertSame('VALID', $expiry->state(CarbonImmutable::parse('2026-08-13'), $asOf, 30));
    }

    public function test_list_filter_uses_explicit_as_of_and_excludes_archived_records(): void
    {
        $employee = $this->employee();
        $type = ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'PASSPORT')->firstOrFail();
        $expiring = $this->document($employee, $type, '2026-07-23');
        $this->document($employee, $type, '2026-08-13');
        $archived = $this->document($employee, $type, '2026-07-20');
        $archived->delete();
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.view');

        $this->actingAs($user)->get(route('hr.employee-documents.index', [
            'as_of' => '2026-07-13',
            'warning_days' => 30,
            'expiry_state' => 'EXPIRING',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('filters.as_of', '2026-07-13')
            ->where('filters.warning_days', 30)
            ->where('filters.expiry_state', 'EXPIRING')
            ->where('documents.total', 1)
            ->where('documents.data.0.id', $expiring->id)
            ->where('documents.data.0.expiry_state', 'EXPIRING'));
    }

    private function document(Employee $employee, ReferenceData $type, ?string $expiresAt): EmployeeDocument
    {
        return EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => $type->id,
            'expires_at' => $expiresAt,
            'verification_status' => 'PENDING',
        ]);
    }

    private function employee(): Employee
    {
        $status = EmploymentStatus::query()->create(['code' => 'ACTIVE', 'name' => 'Active', 'active' => true]);
        $type = EmploymentType::query()->create(['code' => 'PERM', 'name' => 'Permanent', 'active' => true]);

        return Employee::query()->create([
            'employee_number' => 'EMP-EXPIRY', 'first_name' => 'Expiry', 'display_name' => 'Expiry Employee',
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);
    }
}
