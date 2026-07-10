<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Console\LoginActivities\Models\LoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('login-activities.view');
        Role::findOrCreate('admin')->syncPermissions(['login-activities.view']);
    }

    public function test_successful_login_is_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => 'login@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'email' => 'login@example.com',
            'event' => 'login',
            'successful' => true,
        ]);
    }

    public function test_failed_login_is_recorded(): void
    {
        User::factory()->create([
            'email' => 'failed@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => 'failed@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('login_activities', [
            'email' => 'failed@example.com',
            'event' => 'login_failed',
            'successful' => false,
        ]);
    }

    public function test_authorized_users_can_view_login_activities(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        LoginActivity::query()->create([
            'user_id' => $user->id,
            'email' => $user->email,
            'event' => 'login',
            'successful' => true,
            'occurred_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('login-activities.index'))
            ->assertOk();
    }
}
