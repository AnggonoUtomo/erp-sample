<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeDocuments\Models\EmployeeDocument;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeDocumentArchiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['employee-documents.view', 'employee-documents.archive', 'employee-documents.restore'] as $permission) {
            Permission::findOrCreate($permission);
        }
        $this->seed(HRReferenceDataSeeder::class);
    }

    public function test_authorized_user_archives_metadata_and_list_filter_exposes_history(): void
    {
        $document = $this->document();
        $user = User::factory()->create();
        $user->givePermissionTo(['employee-documents.view', 'employee-documents.archive']);

        $this->actingAs($user)->delete(route('hr.employee-documents.destroy', $document))->assertRedirect();

        $this->assertSoftDeleted('hr_employee_documents', ['id' => $document->id]);
        $this->assertNull(EmployeeDocument::withTrashed()->findOrFail($document->id)->document_number_uniqueness_key);
        $this->assertDatabaseHas('audit_logs', ['event' => 'EmployeeDocument.archived', 'actor_id' => $user->id]);
        $this->actingAs($user)->get(route('hr.employee-documents.index', ['archive' => 'only-trashed']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.archive', 'only-trashed')
                ->where('documents.total', 1)
                ->where('documents.data.0.archived', true));
    }

    public function test_restore_revalidates_duplicate_and_keeps_metadata_archived_on_failure(): void
    {
        $document = $this->document();
        $manager = User::factory()->create();
        $manager->givePermissionTo(['employee-documents.archive', 'employee-documents.restore']);
        $this->actingAs($manager)->delete(route('hr.employee-documents.destroy', $document));

        $duplicate = $this->document(['document_number' => 'DOC-001']);
        $this->actingAs($manager)->patch(route('hr.employee-documents.restore', $document))
            ->assertSessionHasErrors('document_number');

        $this->assertTrue(EmployeeDocument::withTrashed()->findOrFail($document->id)->trashed());
        $this->assertFalse($duplicate->trashed());
        $this->assertDatabaseMissing('audit_logs', ['event' => 'EmployeeDocument.restored', 'auditable_id' => $document->id]);
    }

    public function test_restore_rejects_inactive_type_then_succeeds_after_invariant_is_valid(): void
    {
        $document = $this->document(['document_number' => 'DOC-RESTORE']);
        $manager = User::factory()->create();
        $manager->givePermissionTo(['employee-documents.archive', 'employee-documents.restore']);
        $this->actingAs($manager)->delete(route('hr.employee-documents.destroy', $document));
        $document->documentType->update(['active' => false]);

        $this->actingAs($manager)->patch(route('hr.employee-documents.restore', $document))->assertSessionHasErrors('document_type_id');
        $document->documentType()->withTrashed()->firstOrFail()->update(['active' => true]);
        $this->actingAs($manager)->patch(route('hr.employee-documents.restore', $document))->assertSessionDoesntHaveErrors();

        $this->assertNotSoftDeleted('hr_employee_documents', ['id' => $document->id]);
        $this->assertNotNull($document->refresh()->document_number_uniqueness_key);
        $this->assertDatabaseHas('audit_logs', ['event' => 'EmployeeDocument.restored', 'actor_id' => $manager->id]);
    }

    public function test_unauthorized_archive_restore_and_force_delete_route_are_absent(): void
    {
        $document = $this->document();
        $user = User::factory()->create();

        $this->actingAs($user)->delete(route('hr.employee-documents.destroy', $document))->assertForbidden();
        $document->delete();
        $this->actingAs($user)->patch(route('hr.employee-documents.restore', $document))->assertForbidden();
        $this->assertFalse(app('router')->getRoutes()->hasNamedRoute('hr.employee-documents.force-delete'));
    }

    private function document(array $overrides = []): EmployeeDocument
    {
        $status = EmploymentStatus::query()->firstOrCreate(['code' => 'ACTIVE'], ['name' => 'Active', 'active' => true]);
        $employmentType = EmploymentType::query()->firstOrCreate(['code' => 'PERM'], ['name' => 'Permanent', 'active' => true]);
        $employee = Employee::query()->firstOrCreate(['employee_number' => 'EMP-ARCHIVE'], [
            'first_name' => 'Archive', 'display_name' => 'Archive Employee',
            'employment_status_id' => $status->id, 'employment_type_id' => $employmentType->id, 'active' => true,
        ]);
        $type = ReferenceData::query()->where('category', 'employee-document-type')->where('code', 'KTP')->firstOrFail();
        $number = $overrides['document_number'] ?? 'DOC-001';
        $fingerprint = hash_hmac('sha256', preg_replace('/[^A-Z0-9]/', '', strtoupper($number)), config('app.key'));
        $key = hash_hmac('sha256', "GLOBAL|{$type->id}|{$fingerprint}", config('app.key'));

        return EmployeeDocument::query()->create([...[
            'employee_id' => $employee->id, 'document_type_id' => $type->id,
            'document_number' => $number, 'document_number_fingerprint' => $fingerprint,
            'document_number_uniqueness_key' => $key, 'verification_status' => 'PENDING',
        ], ...$overrides]);
    }
}
