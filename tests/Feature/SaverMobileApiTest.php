<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Actions\WithdrawAction;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SaverMobileApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected User $saverUser;

    protected Member $saverMember;

    protected Saver $saver;

    protected User $calonUser;

    protected Member $calonMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        // Admin & Teller
        $this->admin = User::factory()->create([
            'name' => 'Pak Admin',
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create([
            'name' => 'Ibu Teller',
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        // 1. Active Saver
        $this->saverUser = User::factory()->create([
            'name' => 'Ahmad Penabung',
            'username' => 'ahmad_saver',
            'email' => 'ahmad@bankmini.test',
            'password' => Hash::make('password123'),
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $this->saverMember = Member::create([
            'user_id' => $this->saverUser->id,
            'student_name' => 'Ahmad Penabung',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->saverUser->id,
            'member_id' => $this->saverMember->id,
            'account_number' => 'BM20261001',
            'balance' => 50000,
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20261001-SCAN',
            'status' => SaverStatus::Active,
            'opened_at' => now(),
        ]);

        // 2. Inactive Member (not yet deposited min 10k at teller)
        $this->calonUser = User::factory()->create([
            'name' => 'Bambang Siswa Baru',
            'username' => 'bambang_calon',
            'email' => 'bambang@bankmini.test',
            'password' => Hash::make('password123'),
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $this->calonMember = Member::create([
            'user_id' => $this->calonUser->id,
            'student_name' => 'Bambang Siswa Baru',
            'graduation_year' => '2027',
            'account_status' => MemberAccountStatus::Inactive,
        ]);
    }

    public function test_active_saver_can_login_via_saver_login_endpoint(): void
    {
        $response = $this->postJson(route('saver.login'), [
            'login' => 'ahmad_saver',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('is_active_saver', true)
            ->assertJsonPath('role', 'nasabah')
            ->assertJsonPath('saver.account_number', 'BM20261001')
            ->assertJsonPath('saver.qr_code_token', 'QR-BM20261001-SCAN')
            ->assertJsonPath('saver.balance', 50000)
            ->assertJsonStructure([
                'token',
                'user',
                'member',
                'saver',
            ]);
    }

    public function test_inactive_member_receives_status_and_activation_guidance_on_login(): void
    {
        $response = $this->postJson(route('saver.login'), [
            'login' => 'bambang@bankmini.test',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('is_active_saver', false)
            ->assertJsonPath('account_status', 'inactive')
            ->assertJsonPath('saver', null)
            ->assertJsonStructure(['token', 'user', 'member'])
            ->assertSee('calon siswa (member), namun rekening tabungan belum aktif');
    }

    public function test_admin_and_teller_are_forbidden_from_logging_in_via_saver_endpoint(): void
    {
        $adminRes = $this->postJson(route('saver.login'), [
            'login' => $this->admin->email,
            'password' => 'password',
        ]);
        $adminRes->assertForbidden();

        $tellerRes = $this->postJson(route('saver.login'), [
            'login' => $this->teller->email,
            'password' => 'password',
        ]);
        $tellerRes->assertForbidden();
    }

    public function test_saver_logout_revokes_sanctum_token(): void
    {
        $loginRes = $this->postJson(route('saver.login'), [
            'login' => 'ahmad_saver',
            'password' => 'password123',
        ]);

        $token = $loginRes->json('token');

        $logoutRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(route('saver.logout'));

        $logoutRes->assertOk();

        // Token must be removed from database
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // Reset auth state in test runner so next request re-authenticates via token
        app('auth')->forgetGuards();

        // Following request with revoked token must fail with 401
        $profileRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(route('saver.profile'));

        $profileRes->assertUnauthorized();
    }

    public function test_saver_can_access_read_only_profile_and_balance_endpoints(): void
    {
        $response = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('saver.profile'));

        $response->assertOk()
            ->assertJsonPath('is_active_saver', true)
            ->assertJsonPath('summary.account_number', 'BM20261001')
            ->assertJsonPath('summary.balance', 50000)
            ->assertJsonPath('summary.formatted_balance', 'Rp50.000')
            ->assertJsonPath('summary.withdrawable_balance', 40000)
            ->assertJsonPath('summary.minimum_maintaining_balance', 10000)
            ->assertJsonPath('summary.qr_code_token', 'QR-BM20261001-SCAN');

        $balanceRes = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('saver.balance'));

        $balanceRes->assertOk()
            ->assertJsonPath('balance', 50000)
            ->assertJsonPath('withdrawable_balance', 40000)
            ->assertJsonPath('account_number', 'BM20261001');
    }

    public function test_inactive_member_profile_shows_inactive_status(): void
    {
        $response = $this->actingAs($this->calonUser, 'sanctum')
            ->getJson(route('saver.profile'));

        $response->assertOk()
            ->assertJsonPath('is_active_saver', false)
            ->assertJsonPath('saver', null)
            ->assertJsonPath('member.student_name', 'Bambang Siswa Baru');
    }

    public function test_saver_can_view_read_only_digital_passbook_mutations(): void
    {
        // 1. Record deposit and withdrawal
        app(DepositAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 30000,
            notes: 'Setoran Uang Saku'
        );

        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            pin: '123456',
            amount: 15000,
            notes: 'Beli Buku Tulis'
        );

        $response = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('saver.mutations'));

        $response->assertOk()
            ->assertJsonPath('summary.current_balance', 65000)
            ->assertJsonPath('summary.total_deposit', 30000)
            ->assertJsonPath('summary.total_withdrawal', 15000)
            ->assertJsonCount(2, 'data');
    }

    public function test_saver_can_filter_mutations_by_type(): void
    {
        app(DepositAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            amount: 20000,
            notes: 'Setoran Filter'
        );

        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            pin: '123456',
            amount: 10000,
            notes: 'Penarikan Filter'
        );

        $depRes = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('saver.mutations', ['type' => 'deposit']));

        $depRes->assertOk();
        $this->assertEquals(1, count($depRes->json('data')));
        $this->assertEquals('deposit', $depRes->json('data.0.type'));

        $wthRes = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('saver.mutations', ['type' => 'withdrawal']));

        $wthRes->assertOk();
        $this->assertEquals(1, count($wthRes->json('data')));
        $this->assertEquals('withdrawal', $wthRes->json('data.0.type'));
    }

    public function test_saver_pin_verification(): void
    {
        // Valid PIN
        $validRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('saver.pin.verify'), [
                'pin' => '123456',
            ]);

        $validRes->assertOk()
            ->assertJsonPath('valid', true);

        // Invalid PIN
        $invalidRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('saver.pin.verify'), [
                'pin' => '999999',
            ]);

        $invalidRes->assertUnprocessable()
            ->assertJsonPath('valid', false);
    }

    public function test_saver_can_change_pin_with_current_pin(): void
    {
        $response = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('saver.pin.change'), [
                'current_pin' => '123456',
                'new_pin' => '654321',
                'new_pin_confirmation' => '654321',
            ]);

        $response->assertOk();

        $this->saver->refresh();
        $this->assertTrue(Hash::check('654321', $this->saver->pin));
        $this->assertFalse(Hash::check('123456', $this->saver->pin));
    }

    public function test_saver_can_request_and_confirm_forgot_pin_in_app(): void
    {
        // 1. Request token while logged in
        $reqRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('saver.pin.forgot-request'));

        $reqRes->assertCreated()
            ->assertJsonStructure(['reset_token']);

        $token = $reqRes->json('reset_token');

        // 2. Confirm reset
        $confirmRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('saver.pin.forgot-confirm'), [
                'reset_token' => $token,
                'new_pin' => '888888',
                'new_pin_confirmation' => '888888',
            ]);

        $confirmRes->assertOk();

        $this->saver->refresh();
        $this->assertTrue(Hash::check('888888', $this->saver->pin));
    }

    public function test_rbac_saver_is_strictly_prevented_from_admin_and_teller_actions(): void
    {
        // Cannot access admin users
        $adminRes = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('admin.users.index'));
        $adminRes->assertForbidden();

        // Cannot access admin audit journals
        $journalRes = $this->actingAs($this->saverUser, 'sanctum')
            ->getJson(route('admin.audit-journals.index'));
        $journalRes->assertForbidden();

        // Cannot manipulate teller deposit transactions
        $depositRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('teller.transactions.deposit'), [
                'saver_id' => $this->saver->id,
                'amount' => 1000000,
            ]);
        $depositRes->assertForbidden();

        // Cannot manipulate teller cash closure
        $closureRes = $this->actingAs($this->saverUser, 'sanctum')
            ->postJson(route('teller.cash-closure.submit'), [
                'physical_ending_balance' => 500000,
            ]);
        $closureRes->assertForbidden();
    }

    public function test_rbac_teller_cannot_access_saver_endpoints(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')
            ->getJson(route('saver.profile'));

        $response->assertForbidden();
    }

    public function test_unauthenticated_request_to_saver_endpoints_is_unauthorized(): void
    {
        $response = $this->getJson(route('saver.profile'));
        $response->assertUnauthorized();
    }
}
