<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('roles.manage');
        Permission::findOrCreate('users.view');
        Permission::findOrCreate('users.create');

        Role::findOrCreate('admin')->syncPermissions(['roles.manage']);
    }

    public function test_authorized_users_can_view_access_control(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Role::findOrCreate(User::SUPER_SYSTEM_ROLE)->syncPermissions(['roles.manage']);

        $this->actingAs($user)
            ->get(route('access-control.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('roles', fn ($roles) => ! collect($roles)->pluck('name')->contains(User::SUPER_SYSTEM_ROLE))
                ->etc()
            );
    }

    public function test_authorized_users_can_create_role_with_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('access-control.roles.store'), [
                'name' => 'editor',
                'permissions' => ['users.view', 'users.create'],
            ])
            ->assertRedirect();

        $this->assertTrue(Role::findByName('editor')->hasPermissionTo('users.create'));
    }

    public function test_authorized_users_can_create_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->post(route('access-control.permissions.store'), [
                'name' => 'reports.view',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', [
            'name' => 'reports.view',
            'guard_name' => 'web',
        ]);
    }

    public function test_authorized_users_can_sync_role_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $role = Role::findOrCreate('editor');

        $this->actingAs($user)
            ->put(route('access-control.roles.permissions.sync', $role), [
                'permissions' => ['users.view', 'users.create'],
            ])
            ->assertRedirect();

        $this->assertTrue($role->refresh()->hasPermissionTo('users.view'));
        $this->assertTrue($role->hasPermissionTo('users.create'));
    }

    public function test_users_without_permission_cannot_mutate_access_control(): void
    {
        $user = User::factory()->create();
        $role = Role::findOrCreate('editor');
        $permission = Permission::findOrCreate('reports.view');

        $requests = [
            ['post', route('access-control.roles.store')],
            ['put', route('access-control.roles.update', $role)],
            ['delete', route('access-control.roles.destroy', $role)],
            ['put', route('access-control.roles.permissions.sync', $role)],
            ['post', route('access-control.permissions.store')],
            ['delete', route('access-control.permissions.destroy', $permission)],
        ];

        foreach ($requests as [$method, $url]) {
            $this->actingAs($user)->{$method}($url)->assertForbidden();
        }
    }

    public function test_super_system_role_is_hidden_and_cannot_be_mutated_directly(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $role = Role::findOrCreate(User::SUPER_SYSTEM_ROLE);

        $this->actingAs($user)
            ->put(route('access-control.roles.update', $role), [
                'name' => 'renamed-super-system',
                'permissions' => ['users.view'],
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('access-control.roles.permissions.sync', $role), [
                'permissions' => ['users.view'],
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('access-control.roles.destroy', $role))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('access-control.roles.store'), [
                'name' => User::SUPER_SYSTEM_ROLE,
                'permissions' => ['users.view'],
            ])
            ->assertSessionHasErrors('name');
    }
}
