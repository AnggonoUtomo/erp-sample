<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\EmployeeDocuments\Support\EmployeeDocumentTypeCatalog;
use App\Modules\HR\HRReferenceData\Database\Seeders\HRReferenceDataSeeder;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HREmployeeDocumentTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['hr-reference-data.create', 'hr-reference-data.update'] as $permission) {
            Permission::findOrCreate($permission);
        }
    }

    public function test_seeder_publishes_stable_employee_document_type_contract_idempotently(): void
    {
        $this->seed(HRReferenceDataSeeder::class);
        $this->seed(HRReferenceDataSeeder::class);

        $this->assertDatabaseHas('hr_reference_categories', [
            'code' => EmployeeDocumentTypeCatalog::CATEGORY,
            'active' => true,
        ]);
        $this->assertSame(
            ['CERTIFICATE', 'CONTRACT', 'KTP', 'MEDICAL', 'NPWP', 'OTHER', 'PASSPORT'],
            ReferenceData::query()->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->orderBy('code')->pluck('code')->all(),
        );

        $passport = ReferenceData::query()->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('code', 'PASSPORT')->firstOrFail();
        $this->assertSame([
            'requires_expiry' => true,
            'requires_number' => true,
            'number_unique_scope' => 'GLOBAL',
        ], $passport->metadata);
    }

    public function test_document_type_metadata_contract_is_validated_at_reference_data_boundary(): void
    {
        $this->seed(HRReferenceDataSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo(['hr-reference-data.create', 'hr-reference-data.update']);

        $this->actingAs($user)->post(route('hr.hr-reference-data.store'), [
            'category' => EmployeeDocumentTypeCatalog::CATEGORY,
            'code' => 'VISA',
            'name' => 'Visa',
            'active' => true,
            'metadata' => ['requires_expiry' => true, 'requires_number' => true, 'number_unique_scope' => 'INVALID'],
        ])->assertSessionHasErrors('metadata.number_unique_scope');

        $this->actingAs($user)->post(route('hr.hr-reference-data.store'), [
            'category' => EmployeeDocumentTypeCatalog::CATEGORY,
            'code' => 'VISA',
            'name' => 'Visa',
            'active' => true,
            'metadata' => ['requires_expiry' => true, 'requires_number' => true, 'number_unique_scope' => 'GLOBAL', 'typo' => true],
        ])->assertSessionHasErrors('metadata');
    }

    public function test_catalog_excludes_inactive_or_archived_types_from_input_but_resolves_history(): void
    {
        $this->seed(HRReferenceDataSeeder::class);
        $catalog = app(EmployeeDocumentTypeCatalog::class);
        $ktp = ReferenceData::query()->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('code', 'KTP')->firstOrFail();
        $npwp = ReferenceData::query()->where('category', EmployeeDocumentTypeCatalog::CATEGORY)->where('code', 'NPWP')->firstOrFail();
        $ktp->update(['active' => false]);
        $npwp->delete();

        $inputIds = $catalog->inputOptions()->pluck('id');
        $this->assertNotContains($ktp->id, $inputIds);
        $this->assertNotContains($npwp->id, $inputIds);
        $this->assertSame('NPWP', $catalog->resolveForHistory($npwp->id)?->code);
        $this->assertNull($catalog->resolveForHistory(999999));
    }
}
