<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/console/login');

        $response->assertStatus(200);
    }

    public function test_hr_login_screen_can_be_rendered()
    {
        $response = $this->get('/hr/login');

        $response->assertStatus(200);
    }

    public function test_legacy_login_url_redirects_to_console_login()
    {
        $this->get('/login')->assertRedirect('/console/login');
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/console/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_return_to_project_page_after_login_with_redirect()
    {
        $user = User::factory()->create();

        $this->get('/console/login?redirect=/hr/departements')->assertOk();

        $response = $this->post('/console/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/hr/departements');
    }

    public function test_users_can_authenticate_using_the_hr_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/hr/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('hr.dashboard', absolute: false));
    }

    public function test_hr_project_redirects_guests_to_hr_login()
    {
        $this->get('/hr/dashboard')->assertRedirect('/hr/login');
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/console/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
