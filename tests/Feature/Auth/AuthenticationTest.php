<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('nasabah.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_users_can_authenticate_using_username(): void
    {
        $user = User::factory()->create([
            'username' => 'teller_utama',
            'role' => Role::Teller,
        ]);

        $response = $this->post('/login', [
            'email' => 'teller_utama',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teller.counter', absolute: false));
    }

    public function test_admin_is_redirected_to_admin_dashboard_on_login(): void
    {
        $admin = User::factory()->create([
            'role' => Role::Admin,
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard', absolute: false));
    }

    public function test_guest_accessing_home_is_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_visiting_home_or_login_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);

        $responseHome = $this->actingAs($admin)->get('/');
        $responseHome->assertRedirect(route('admin.dashboard'));

        $responseLogin = $this->actingAs($admin)->get('/login');
        $responseLogin->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_teller_visiting_home_or_login_is_redirected_to_teller_counter(): void
    {
        $teller = User::factory()->create(['role' => Role::Teller]);

        $responseHome = $this->actingAs($teller)->get('/');
        $responseHome->assertRedirect(route('teller.counter'));

        $responseLogin = $this->actingAs($teller)->get('/login');
        $responseLogin->assertRedirect(route('teller.counter'));
    }

    public function test_authenticated_nasabah_visiting_home_or_login_is_redirected_to_nasabah_dashboard(): void
    {
        $nasabah = User::factory()->create(['role' => Role::Nasabah]);

        $responseHome = $this->actingAs($nasabah)->get('/');
        $responseHome->assertRedirect(route('nasabah.dashboard'));

        $responseLogin = $this->actingAs($nasabah)->get('/login');
        $responseLogin->assertRedirect(route('nasabah.dashboard'));
    }

    public function test_inactive_users_can_not_authenticate(): void
    {
        $user = User::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.']);
    }
}
