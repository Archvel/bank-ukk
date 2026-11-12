<?php

namespace Tests\Feature;

use App\Enums\CashClosureStatus;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\CashClosure;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
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

    public function test_deleting_member_with_empty_soft_deleted_saver_succeeds_and_blocked_when_transactions_exist(): void
    {
        // Case 1: Soft-deleted saver with 0 transactions and 0 balance
        $userEmpty = User::factory()->create(['role' => Role::Nasabah]);
        $memberEmpty = Member::create([
            'user_id' => $userEmpty->id,
            'student_name' => 'Siswa Kosong',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);
        $saverEmpty = Saver::create([
            'user_id' => $userEmpty->id,
            'member_id' => $memberEmpty->id,
            'account_number' => 'BM20268001',
            'pin' => bcrypt('123456'),
            'qr_code_token' => 'QR-BM20268001',
            'balance' => 0,
            'status' => SaverStatus::Active,
        ]);
        $saverEmpty->delete();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('admin.api.members.destroy', $memberEmpty))
            ->assertOk();

        $this->assertDatabaseMissing('savers', ['id' => $saverEmpty->id]);
        $this->assertDatabaseMissing('members', ['id' => $memberEmpty->id]);
        $this->assertDatabaseMissing('users', ['id' => $userEmpty->id]);

        // Case 2: Soft-deleted saver WITH a transaction must be blocked cleanly (422)
        $teller = User::factory()->create(['role' => Role::Teller]);
        $userTrx = User::factory()->create(['role' => Role::Nasabah]);
        $memberTrx = Member::create([
            'user_id' => $userTrx->id,
            'student_name' => 'Siswa Bertransaksi',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);
        $saverTrx = Saver::create([
            'user_id' => $userTrx->id,
            'member_id' => $memberTrx->id,
            'account_number' => 'BM20268002',
            'pin' => bcrypt('123456'),
            'qr_code_token' => 'QR-BM20268002',
            'balance' => 10000,
            'status' => SaverStatus::Active,
        ]);
        Transaction::create([
            'transaction_number' => 'TRX-20261112-8002',
            'saver_id' => $saverTrx->id,
            'teller_id' => $teller->id,
            'type' => 'deposit',
            'amount' => 10000,
            'opening_balance' => 0,
            'closing_balance' => 10000,
        ]);
        $saverTrx->delete();

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('admin.api.members.destroy', $memberTrx))
            ->assertUnprocessable();

        $this->assertDatabaseHas('members', ['id' => $memberTrx->id]);
        $this->assertDatabaseHas('savers', ['id' => $saverTrx->id]);
    }

    public function test_deleting_user_handles_member_and_cash_closure_constraints_safely(): void
    {
        // Case 1: Teller with a CashClosure record must be blocked cleanly (422, not SQL 500)
        $teller = User::factory()->create(['role' => Role::Teller]);
        CashClosure::create([
            'teller_id' => $teller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 0,
            'total_withdrawal' => 0,
            'system_ending_balance' => 0,
            'physical_ending_balance' => 0,
            'difference' => 0,
            'status' => CashClosureStatus::Draft,
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('admin.api.users.destroy', $teller))
            ->assertUnprocessable();
        $this->assertDatabaseHas('users', ['id' => $teller->id]);

        // Case 2: Nasabah user with inactive Member (0 saver) deletes cleanly
        $studentUser = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $studentUser->id,
            'student_name' => 'Siswa Belum Aktif',
            'graduation_year' => '2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->deleteJson(route('admin.api.users.destroy', $studentUser))
            ->assertOk();

        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        $this->assertDatabaseMissing('users', ['id' => $studentUser->id]);
    }
}
