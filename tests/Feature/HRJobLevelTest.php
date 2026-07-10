<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\HR\JobLevels\Models\JobLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HRJobLevelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'hr.view',
            'job-levels.view',
            'job-levels.create',
            'job-levels.update',
            'job-levels.delete',
            'job-levels.restore',
            'job-levels.force-delete',
            'job-levels.manage',
        ] as $permission) {
            Permission::findOrCreate($permission);
        }

        Role::findOrCreate('admin')->syncPermissions([
            'hr.view',
            'job-levels.view',
            'job-levels.create',
            'job-levels.update',
            'job-levels.delete',
            'job-levels.restore',
            'job-levels.force-delete',
            'job-levels.manage',
        ]);
    }

    public function test_authorized_users_can_view_job_levels(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('hr.job-levels.index'))
            ->assertOk();
    }

    public function test_authorized_users_can_create_job_level(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('hr.job-levels.store'), [
                'code' => 'L1',
                'name' => 'Entry Level',
                'description' => 'Junior individual contributor',
                'active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_job_levels', [
            'code' => 'L1',
            'name' => 'Entry Level',
            'active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_users_can_update_job_level(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $jobLevel = JobLevel::query()->create([
            'code' => 'L2',
            'name' => 'Officer',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->put(route('hr.job-levels.update', $jobLevel), [
                'code' => 'L2',
                'name' => 'Senior Officer',
                'description' => 'Updated',
                'active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('hr_job_levels', [
            'id' => $jobLevel->id,
            'name' => 'Senior Officer',
            'active' => false,
        ]);
    }

    public function test_authorized_users_can_soft_delete_job_level(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $jobLevel = JobLevel::query()->create([
            'code' => 'TMP',
            'name' => 'Temporary Level',
            'active' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('hr.job-levels.destroy', $jobLevel))
            ->assertRedirect();

        $this->assertSoftDeleted('hr_job_levels', [
            'id' => $jobLevel->id,
        ]);
    }

    public function test_authorized_users_can_restore_job_level(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $jobLevel = JobLevel::query()->create([
            'code' => 'RST',
            'name' => 'Restore Level',
            'active' => true,
        ]);
        $jobLevel->delete();

        $this->actingAs($user)
            ->patch(route('hr.job-levels.restore', $jobLevel->id))
            ->assertRedirect();

        $this->assertDatabaseHas('hr_job_levels', [
            'id' => $jobLevel->id,
            'deleted_at' => null,
        ]);
    }

    public function test_authorized_users_can_force_delete_job_level(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $jobLevel = JobLevel::query()->create([
            'code' => 'DEL',
            'name' => 'Delete Level',
            'active' => true,
        ]);
        $jobLevel->delete();

        $this->actingAs($user)
            ->delete(route('hr.job-levels.force-destroy', $jobLevel->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('hr_job_levels', [
            'id' => $jobLevel->id,
        ]);
    }
}
