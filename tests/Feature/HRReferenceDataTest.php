<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\HRReferenceData\Models\ReferenceCategory;
use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HRReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach ([
            'hr.view',
            'hr-reference-data.view',
            'hr-reference-data.create',
            'hr-reference-data.update',
            'hr-reference-data.delete',
            'hr-reference-data.restore',
            'hr-reference-data.force-delete',
            'hr-reference-data.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'hr-reference-data.view',
            'hr-reference-data.create',
            'hr-reference-data.update',
            'hr-reference-data.delete',
            'hr-reference-data.restore',
            'hr-reference-data.force-delete',
            'hr-reference-data.manage',
        ]);
    }

    public function test_authorized_users_can_view_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->category('gender', 'Gender');

        $this->actingAs($user)
            ->get(route('hr.hr-reference-data.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->category('gender', 'Gender');

        $this->actingAs($user)
            ->post(route('hr.hr-reference-data.store'), [
                'category' => 'gender',
                'code' => 'MALE',
                'name' => 'Male',
                'description' => 'Pilihan gender employee.',
                'metadata' => ['external_code' => 'M'],
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_reference_data', [
            'category' => 'gender',
            'code' => 'MALE',
            'name' => 'Male',
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_users_can_update_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->category('bank', 'Bank');
        $referenceData = ReferenceData::query()->create([
            'category' => 'bank',
            'code' => 'BCA',
            'name' => 'Bank Central Asia',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.hr-reference-data.update', $referenceData), [
                'category' => 'bank',
                'code' => 'BCA',
                'name' => 'BCA',
                'description' => 'Updated',
                'metadata' => ['swift' => 'CENAIDJA'],
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_reference_data', [
            'id' => $referenceData->id,
            'category' => 'bank',
            'code' => 'BCA',
            'name' => 'BCA',
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_soft_delete_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $referenceData = $this->referenceData();

        $this->actingAs($user)
            ->delete(route('hr.hr-reference-data.destroy', $referenceData))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_reference_data', [
            'id' => $referenceData->id,
        ]);
    }

    public function test_authorized_users_can_restore_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $referenceData = $this->referenceData('RESTORE');
        $referenceData->delete();

        $this->actingAs($user)
            ->patch(route('hr.hr-reference-data.restore', $referenceData->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_reference_data', [
            'id' => $referenceData->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_hr_reference_data(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $referenceData = $this->referenceData('DELETE');
        $referenceData->delete();

        $this->actingAs($user)
            ->delete(route('hr.hr-reference-data.force-destroy', $referenceData->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_reference_data', [
            'id' => $referenceData->id,
        ]);
    }

    public function test_authorized_users_can_create_reference_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.hr-reference-data.categories.store'), [
                'name' => 'Document Type',
                'description' => 'Kategori jenis dokumen employee.',
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_reference_categories', [
            'code' => 'document-type',
            'name' => 'Document Type',
            'active' => true,
        ]);
    }

    public function test_authorized_users_can_update_reference_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $category = $this->category('old-bank', 'Old Bank');

        $this->actingAs($user)
            ->put(route('hr.hr-reference-data.categories.update', $category), [
                'code' => 'bank',
                'name' => 'Bank',
                'description' => 'Updated',
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_reference_categories', [
            'id' => $category->id,
            'code' => 'bank',
            'name' => 'Bank',
            'active' => false,
        ]);
    }

    public function test_reference_category_cannot_be_deleted_when_used(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $category = $this->category('gender', 'Gender');
        $this->referenceData('MALE', 'gender');

        $this->actingAs($user)
            ->delete(route('hr.hr-reference-data.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('hr_reference_categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_delete_unused_reference_category(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $category = $this->category('unused', 'Unused');

        $this->actingAs($user)
            ->delete(route('hr.hr-reference-data.categories.destroy', $category))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_reference_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_users_without_permission_cannot_mutate_reference_data_or_categories(): void
    {
        $user = User::factory()->create();
        $category = $this->category('deny', 'Denied');
        $reference = $this->referenceData('DENY', 'deny');

        $this->actingAs($user)->post(route('hr.hr-reference-data.store'))->assertForbidden();
        $this->actingAs($user)->put(route('hr.hr-reference-data.update', $reference))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.hr-reference-data.destroy', $reference))->assertForbidden();
        $this->actingAs($user)->post(route('hr.hr-reference-data.categories.store'))->assertForbidden();
        $this->actingAs($user)->put(route('hr.hr-reference-data.categories.update', $category))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.hr-reference-data.categories.destroy', $category))->assertForbidden();
        $reference->delete();
        $this->actingAs($user)->patch(route('hr.hr-reference-data.restore', $reference->id))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.hr-reference-data.force-destroy', $reference->id))->assertForbidden();
        $this->assertSoftDeleted('hr_reference_data', ['id' => $reference->id]);
    }

    private function category(string $code = 'test-category', string $name = 'Test Category'): ReferenceCategory
    {
        return ReferenceCategory::query()->create([
            'code' => $code,
            'name' => $name,
            'active' => true,
        ]);
    }

    private function referenceData(string $code = 'TMP', string $category = 'test-category'): ReferenceData
    {
        ReferenceCategory::query()->firstOrCreate(
            ['code' => $category],
            ['name' => str($category)->replace('-', ' ')->title()->toString(), 'active' => true],
        );

        return ReferenceData::query()->create([
            'category' => $category,
            'code' => $code,
            'name' => "{$code} Reference",
            'active' => true,
        ]);
    }
}
