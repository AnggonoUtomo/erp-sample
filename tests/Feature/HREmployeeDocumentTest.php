<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['employee-documents.view', 'employee-documents.create'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $this->seed(HRReferenceDataSeeder::class);
    }

    public function test_authorized_hr_creates_pending_metadata_with_encrypted_number_and_redacted_audit(): void
    {
        $employee = $this->employee('EMP-DOC-1');
        $ktp = $this->type('KTP');
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.create');

        $this->actingAs($user)->post(route('hr.employee-documents.store'), [
            'employee_id' => $employee->id,
            'document_type_id' => $ktp->id,
            'document_number' => ' 3174-0101-9999-0001 ',
            'issuer' => 'Disdukcapil',
            'issued_at' => '2026-01-10',
            'notes' => 'Metadata only',
        ])->assertRedirect();

        $document = EmployeeDocument::query()->firstOrFail();
        $stored = DB::table('hr_employee_documents')->where('id', $document->id)->first();
        $this->assertSame('PENDING', $document->verification_status);
        $this->assertSame('3174-0101-9999-0001', $document->document_number);
        $this->assertNotSame('3174-0101-9999-0001', $stored->document_number);
        $this->assertNotEmpty($stored->document_number_fingerprint);
        $audit = AuditLog::query()->where('event', 'EmployeeDocument.created')->firstOrFail();
        $this->assertArrayNotHasKey('document_number', $audit->new_values);
        $this->assertArrayNotHasKey('document_number_fingerprint', $audit->new_values);
    }

    public function test_list_is_paginated_filterable_and_only_exposes_masked_document_number(): void
    {
        $employee = $this->employee('EMP-DOC-2');
        $document = EmployeeDocument::query()->create([
            'employee_id' => $employee->id,
            'document_type_id' => $this->type('KTP')->id,
            'document_number' => '3174010199990002',
            'document_number_fingerprint' => hash_hmac('sha256', '3174010199990002', config('app.key')),
            'verification_status' => 'PENDING',
        ]);
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.view');
        $employee->delete();

        $this->actingAs($user)->get(route('hr.employee-documents.index', ['employee' => $employee->id, 'status' => 'PENDING']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/employee-documents/index')
                ->where('documents.total', 1)
                ->where('documents.data.0.id', $document->id)
                ->where('documents.data.0.document_number_masked', '************0002')
                ->missing('documents.data.0.document_number')
                ->has('options.employees')
                ->has('options.documentTypes'));
    }

    public function test_required_expiry_date_order_and_archived_inputs_are_rejected(): void
    {
        $employee = $this->employee('EMP-DOC-3');
        $passport = $this->type('PASSPORT');
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.create');
        $payload = [
            'employee_id' => $employee->id,
            'document_type_id' => $passport->id,
            'document_number' => 'A1234567',
            'issued_at' => '2026-07-13',
        ];

        $this->actingAs($user)->post(route('hr.employee-documents.store'), $payload)->assertSessionHasErrors('expires_at');
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$payload, 'expires_at' => '2026-07-12'])
            ->assertSessionHasErrors('expires_at');

        $withoutIssuedAt = [...$payload, 'expires_at' => '2027-07-13'];
        unset($withoutIssuedAt['issued_at']);
        $this->actingAs($user)->post(route('hr.employee-documents.store'), $withoutIssuedAt)
            ->assertSessionDoesntHaveErrors();

        $passport->delete();
        $employee->delete();
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$payload, 'expires_at' => '2027-07-13'])
            ->assertSessionHasErrors(['employee_id', 'document_type_id']);
    }

    public function test_duplicate_policy_is_enforced_by_type_scope(): void
    {
        $first = $this->employee('EMP-DOC-4');
        $second = $this->employee('EMP-DOC-5');
        $user = User::factory()->create();
        $user->givePermissionTo('employee-documents.create');
        $base = ['document_number' => ' SAME-001 ', 'issued_at' => '2026-01-01'];

        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$base, 'employee_id' => $first->id, 'document_type_id' => $this->type('KTP')->id]);
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$base, 'employee_id' => $second->id, 'document_type_id' => $this->type('KTP')->id])
            ->assertSessionHasErrors('document_number');

        $contract = $this->type('CONTRACT');
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$base, 'employee_id' => $first->id, 'document_type_id' => $contract->id]);
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$base, 'employee_id' => $second->id, 'document_type_id' => $contract->id])
            ->assertSessionDoesntHaveErrors();
        $this->actingAs($user)->post(route('hr.employee-documents.store'), [...$base, 'employee_id' => $first->id, 'document_type_id' => $contract->id])
            ->assertSessionHasErrors('document_number');
    }

    private function type(string $code): ReferenceData
    {
        return ReferenceData::query()->where('category', 'employee-document-type')->where('code', $code)->firstOrFail();
    }

    private function employee(string $number): Employee
    {
        $status = EmploymentStatus::query()->firstOrCreate(['code' => 'ACTIVE'], ['name' => 'Active', 'active' => true]);
        $type = EmploymentType::query()->firstOrCreate(['code' => 'PERM'], ['name' => 'Permanent', 'active' => true]);

        return Employee::query()->create([
            'employee_number' => $number, 'first_name' => $number, 'display_name' => $number,
            'employment_status_id' => $status->id, 'employment_type_id' => $type->id, 'active' => true,
        ]);
    }
}
