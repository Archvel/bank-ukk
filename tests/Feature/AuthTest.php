<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);
    }

    public function test_user_can_login_with_valid_email_and_password(): void
    {
        $user = User::factory()->create([
            'email' => 'teller@bankmini.test',
            'username' => 'teller1',
            'password' => Hash::make('password123'),
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'login' => 'teller@bankmini.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'role'],
            ]);
    }

    public function test_user_can_login_with_username(): void
    {
        User::factory()->create([
            'email' => 'admin@bankmini.test',
            'username' => 'admin_super',
            'password' => Hash::make('password123'),
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'login' => 'admin_super',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.username', 'admin_super');
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@bankmini.test',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(route('auth.login'), [
            'login' => 'admin@bankmini.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['login']);
    }

    public function test_disabled_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@bankmini.test',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson(route('auth.login'), [
            'login' => 'inactive@bankmini.test',
            'password' => 'password123',
        ]);

        $response->assertForbidden();
    }

    public function test_authenticated_user_can_access_profile_and_logout(): void
    {
        $user = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $meResponse = $this->actingAs($user, 'sanctum')->getJson(route('auth.me'));
        $meResponse->assertOk()
            ->assertJsonPath('user.id', $user->id);

        $logoutResponse = $this->actingAs($user, 'sanctum')->postJson(route('auth.logout'));
        $logoutResponse->assertOk();
    }
}
