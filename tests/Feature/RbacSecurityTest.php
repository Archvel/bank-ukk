<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected User $nasabah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $this->nasabah = User::factory()->create([
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);
    }

    public function test_teller_cannot_access_admin_user_management(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')->getJson(route('admin.api.users.index'));
        $response->assertForbidden();
    }

    public function test_nasabah_cannot_access_teller_transactions(): void
    {
        $response = $this->actingAs($this->nasabah, 'sanctum')->getJson(route('teller.transactions.today'));
        $response->assertForbidden();
    }

    public function test_admin_cannot_execute_teller_transactions(): void
    {
        // Enforce segregation of duties
        $response = $this->actingAs($this->admin, 'sanctum')->postJson(route('teller.transactions.deposit'), [
            'amount' => 50000,
        ]);
        $response->assertForbidden();
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson(route('admin.api.users.index'));
        $response->assertUnauthorized();
    }
}
