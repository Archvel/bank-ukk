<?php

namespace Tests\Feature;

use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_crud_users(): void
    {
        // 1. Create a new teller
        $storeResponse = $this->actingAs($this->admin, 'sanctum')->postJson(route('admin.api.users.store'), [
            'name' => 'Teller Baru',
            'username' => 'teller_baru',
            'email' => 'tellerbaru@bankmini.test',
            'password' => 'password123',
            'role' => 'teller',
            'phone_number' => '08123456789',
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('user.username', 'teller_baru')
            ->assertJsonPath('user.role', 'teller');

        $userId = $storeResponse->json('user.id');

        // 2. Index users
        $indexResponse = $this->actingAs($this->admin, 'sanctum')->getJson(route('admin.api.users.index', ['search' => 'teller_baru']));
        $indexResponse->assertOk()
            ->assertJsonCount(1, 'data');

        // 3. Update user
        $updateResponse = $this->actingAs($this->admin, 'sanctum')->putJson(route('admin.api.users.update', $userId), [
            'name' => 'Teller Diperbarui',
            'role' => 'teller',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('user.name', 'Teller Diperbarui');

        // 4. Delete user
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')->deleteJson(route('admin.api.users.destroy', $userId));
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson(route('admin.api.users.destroy', $this->admin->id));
        $response->assertUnprocessable();
    }

    public function test_admin_can_crud_members(): void
    {
        // 1. Create a member
        $storeResponse = $this->actingAs($this->admin, 'sanctum')->postJson(route('admin.api.members.store'), [
            'student_name' => 'Ahmad Santoso',
            'email' => 'ahmad@bankmini.test',
            'username' => 'ahmad2026',
            'graduation_year' => '2026',
        ]);

        $storeResponse->assertCreated()
            ->assertJsonPath('member.student_name', 'Ahmad Santoso')
            ->assertJsonPath('member.account_status', MemberAccountStatus::Inactive->value);

        $memberId = $storeResponse->json('member.id');

        // 2. Update member
        $updateResponse = $this->actingAs($this->admin, 'sanctum')->putJson(route('admin.api.members.update', $memberId), [
            'student_name' => 'Ahmad Santoso SP',
            'graduation_year' => '2027',
        ]);
        $updateResponse->assertOk()
            ->assertJsonPath('member.student_name', 'Ahmad Santoso SP')
            ->assertJsonPath('member.graduation_year', '2027');

        // 3. Delete member
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')->deleteJson(route('admin.api.members.destroy', $memberId));
        $deleteResponse->assertOk();
        $this->assertDatabaseMissing('members', ['id' => $memberId]);
    }

    public function test_admin_can_view_journal_audit_and_summary(): void
    {
        $indexResponse = $this->actingAs($this->admin, 'sanctum')->getJson(route('admin.api.audit-journals.index'));
        $indexResponse->assertOk();

        $summaryResponse = $this->actingAs($this->admin, 'sanctum')->getJson(route('admin.api.audit-journals.summary'));
        $summaryResponse->assertOk()
            ->assertJsonStructure(['total_debit', 'total_credit', 'is_balanced']);
    }

    public function test_admin_can_read_system_settings(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->getJson(route('admin.api.system-settings'));
        $response->assertOk()
            ->assertJsonPath('min_maintaining_balance', 10000)
            ->assertJsonPath('min_initial_deposit', 10000);
    }
}
