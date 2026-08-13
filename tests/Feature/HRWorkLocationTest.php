<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HRWorkLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'hr.view',
            'work-locations.view',
            'work-locations.create',
            'work-locations.update',
            'work-locations.delete',
            'work-locations.restore',
            'work-locations.force-delete',
            'work-locations.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'work-locations.view',
            'work-locations.create',
            'work-locations.update',
            'work-locations.delete',
            'work-locations.restore',
            'work-locations.force-delete',
            'work-locations.manage',
        ]);
    }

    public function test_authorized_users_can_view_work_locations(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.work-locations.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_work_location(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.work-locations.store'), [
                'code' => 'HQ-JKT',
                'name' => 'Head Office Jakarta',
                'address' => 'Jl. Jend. Sudirman Kav. 52-53',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'postal_code' => '12190',
                'timezone' => 'Asia/Jakarta',
                'latitude' => '-6.2240000',
                'longitude' => '106.8099000',
                'geofence_radius_meters' => 150,
                'description' => 'Head office',
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_work_locations', [
            'code' => 'HQ-JKT',
            'name' => 'Head Office Jakarta',
            'city' => 'Jakarta',
            'timezone' => 'Asia/Jakarta',
            'geofence_radius_meters' => 150,
            'sort_order' => 1,
        ]);

        $location = WorkLocation::query()->where('code', 'HQ-JKT')->firstOrFail();

        $this->assertSame('-6.2240000', (string) $location->latitude);
        $this->assertSame('106.8099000', (string) $location->longitude);
    }

    public function test_authorized_users_can_update_work_location(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $workLocation = WorkLocation::query()->create([
            'code' => 'BR-BDG',
            'name' => 'Bandung Branch',
            'city' => 'Bandung',
            'country' => 'Indonesia',
            'timezone' => 'Asia/Jakarta',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.work-locations.update', $workLocation), [
                'code' => 'BR-BDG',
                'name' => 'Bandung Operation Branch',
                'address' => 'Jl. Asia Afrika No. 88',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'country' => 'Indonesia',
                'postal_code' => '40111',
                'timezone' => 'Asia/Jakarta',
                'latitude' => '-6.9216000',
                'longitude' => '107.6070000',
                'geofence_radius_meters' => 120,
                'description' => 'Updated',
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_work_locations', [
            'id' => $workLocation->id,
            'name' => 'Bandung Operation Branch',
            'active' => false,
            'geofence_radius_meters' => 120,
        ]);

        $workLocation->refresh();

        $this->assertSame('-6.9216000', (string) $workLocation->latitude);
        $this->assertSame('107.6070000', (string) $workLocation->longitude);
    }

    public function test_work_location_requires_valid_timezone(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.work-locations.store'), [
                'code' => 'BAD-TZ',
                'name' => 'Invalid Timezone',
                'country' => 'Indonesia',
                'timezone' => 'Not/A_Timezone',
                'active' => true,
            ])
            ->assertSessionHasErrors('timezone');
    }

    public function test_work_location_requires_valid_coordinates_and_geofence_radius(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.work-locations.store'), [
                'code' => 'BAD-GEO',
                'name' => 'Invalid Geofence',
                'country' => 'Indonesia',
                'timezone' => 'Asia/Jakarta',
                'latitude' => -91,
                'longitude' => 181,
                'geofence_radius_meters' => -1,
                'active' => true,
            ])
            ->assertSessionHasErrors(['latitude', 'longitude', 'geofence_radius_meters']);
    }

    public function test_authorized_users_can_soft_delete_work_location(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $workLocation = $this->workLocation();

        $this->actingAs($user)
            ->delete(route('hr.work-locations.destroy', $workLocation))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_work_locations', [
            'id' => $workLocation->id,
        ]);
    }

    public function test_authorized_users_can_restore_work_location(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $workLocation = $this->workLocation('RST');
        $workLocation->delete();

        $this->actingAs($user)
            ->patch(route('hr.work-locations.restore', $workLocation->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_work_locations', [
            'id' => $workLocation->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_work_location(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $workLocation = $this->workLocation('DEL');
        $workLocation->delete();

        $this->actingAs($user)
            ->delete(route('hr.work-locations.force-destroy', $workLocation->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_work_locations', [
            'id' => $workLocation->id,
        ]);
    }

    public function test_users_without_permission_cannot_mutate_work_locations(): void
    {
        $user = User::factory()->create();
        $location = $this->workLocation('DENY');

        $this->actingAs($user)->post(route('hr.work-locations.store'))->assertForbidden();
        $this->actingAs($user)->put(route('hr.work-locations.update', $location))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.work-locations.destroy', $location))->assertForbidden();
        $location->delete();
        $this->actingAs($user)->patch(route('hr.work-locations.restore', $location->id))->assertForbidden();
        $this->actingAs($user)->delete(route('hr.work-locations.force-destroy', $location->id))->assertForbidden();
        $this->assertSoftDeleted('hr_work_locations', ['id' => $location->id]);
    }

    private function workLocation(string $code = 'TMP'): WorkLocation
    {
        return WorkLocation::query()->create([
            'code' => $code,
            'name' => "{$code} Location",
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'timezone' => 'Asia/Jakarta',
            'active' => true,
        ]);
    }
}
