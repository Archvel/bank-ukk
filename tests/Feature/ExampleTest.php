<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest accesses root URL and gets landing page.
     */
    public function test_guest_can_view_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Bank Mini');
        $response->assertSee('Unduh Berkas .APK');
        $response->assertSee('Login');
        $response->assertSee('Aktivasi Otomatis');
    }

    /**
     * Authenticated admin is redirected to admin dashboard.
     */
    public function test_authenticated_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => Role::Admin->value,
        ]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Authenticated teller is redirected to teller counter.
     */
    public function test_authenticated_teller_is_redirected_to_teller_counter(): void
    {
        $teller = User::factory()->create([
            'role' => Role::Teller->value,
        ]);

        $response = $this->actingAs($teller)->get('/');

        $response->assertRedirect(route('teller.counter'));
    }

    /**
     * Authenticated nasabah is redirected to nasabah dashboard.
     */
    public function test_authenticated_nasabah_is_redirected_to_nasabah_dashboard(): void
    {
        $nasabah = User::factory()->create([
            'role' => Role::Nasabah->value,
        ]);

        $response = $this->actingAs($nasabah)->get('/');

        $response->assertRedirect(route('nasabah.dashboard'));
    }
}
