<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Actions\WithdrawAction;
use App\Enums\CashClosureStatus;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\CashClosure;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TellerOpeningCashTest extends TestCase
{
    use RefreshDatabase;

    protected User $teller;

    protected User $admin;

    protected User $nasabah;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-03 08:30:00', 'Asia/Jakarta'));
        $this->seed(AccountSeeder::class);

        $this->teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role' => Role::Admin,
            'is_active' => true,
        ]);

        $this->nasabah = User::factory()->create([
            'role' => Role::Nasabah,
            'is_active' => true,
        ]);

        $member = Member::create([
            'user_id' => $this->nasabah->id,
            'student_name' => 'Siswa Test',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->nasabah->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260099',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260099',
            'balance' => 200000,
            'status' => SaverStatus::Active,
        ]);
    }

    public function test_teller_can_input_opening_cash_at_session_start_via_web(): void
    {
        $response = $this->actingAs($this->teller)
            ->post(route('teller.opening-cash.store'), [
                'opening_balance' => 500000,
                'notes' => 'Modal awal uang kembalian pecahan 10k dan 20k',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_closures', [
            'teller_id' => $this->teller->id,
            'opening_balance' => 500000,
            'status' => CashClosureStatus::Draft->value,
            'notes' => 'Modal awal uang kembalian pecahan 10k dan 20k',
        ]);
    }

    public function test_teller_can_input_opening_cash_via_json_api(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')
            ->postJson(route('teller.opening-cash.store'), [
                'opening_balance' => 300000,
                'notes' => 'Modal kasir shift pagi',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('closure.opening_balance', 300000)
            ->assertJsonPath('closure.system_ending_balance', 300000)
            ->assertJsonPath('closure.status', CashClosureStatus::Draft->value);
    }

    public function test_teller_can_update_opening_cash_before_submitting_daily_closure(): void
    {
        // 1. Initial input: 200.000
        $this->actingAs($this->teller)->post(route('teller.opening-cash.store'), [
            'opening_balance' => 200000,
        ]);

        $this->assertDatabaseHas('cash_closures', [
            'teller_id' => $this->teller->id,
            'opening_balance' => 200000,
        ]);

        // 2. Adjust input to 400.000
        $response = $this->actingAs($this->teller)->post(route('teller.opening-cash.store'), [
            'opening_balance' => 400000,
            'notes' => 'Koreksi penambahan modal uang pecahan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cash_closures', [
            'teller_id' => $this->teller->id,
            'opening_balance' => 400000,
            'notes' => 'Koreksi penambahan modal uang pecahan',
        ]);
    }

    public function test_opening_cash_is_accurately_factored_into_system_ending_balance_and_reconciliation(): void
    {
        // 1. Set opening cash: 500.000
        $this->actingAs($this->teller)->post(route('teller.opening-cash.store'), [
            'opening_balance' => 500000,
        ]);

        // 2. Teller serves 1 deposit (100.000)
        app(DepositAction::class)->handle(
            teller: $this->teller,
            amount: 100000,
            saver: $this->saver
        );

        // 3. Teller serves 1 withdrawal (40.000)
        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            pin: '123456',
            amount: 40000
        );

        // Expected system ending balance: 500.000 (kas awal) + 100.000 (masuk) - 40.000 (keluar) = 560.000
        $calcResponse = $this->actingAs($this->teller, 'sanctum')->getJson(route('teller.cash-closure.calculate'));
        $calcResponse->assertOk()
            ->assertJsonPath('preview.opening_balance', 500000)
            ->assertJsonPath('preview.total_deposit', 100000)
            ->assertJsonPath('preview.total_withdrawal', 40000)
            ->assertJsonPath('preview.system_ending_balance', 560000);

        // 4. Submit closure with exact physical cash count: 560.000
        $submitResponse = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.cash-closure.submit'), [
            'physical_ending_balance' => 560000,
            'notes' => 'Penutupan kas harian seimbang dengan kas awal.',
        ]);

        $submitResponse->assertCreated()
            ->assertJsonPath('closure.system_ending_balance', 560000)
            ->assertJsonPath('closure.physical_ending_balance', 560000)
            ->assertJsonPath('closure.difference', 0)
            ->assertJsonPath('closure.status', CashClosureStatus::Submitted->value);
    }

    public function test_teller_cannot_modify_opening_cash_after_closure_is_submitted(): void
    {
        // Create an already submitted closure
        CashClosure::create([
            'teller_id' => $this->teller->id,
            'closure_date' => today(),
            'opening_balance' => 300000,
            'total_deposit' => 0,
            'total_withdrawal' => 0,
            'system_ending_balance' => 300000,
            'physical_ending_balance' => 300000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.opening-cash.store'), [
            'opening_balance' => 500000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['opening_balance']);
    }

    public function test_opening_cash_rejects_negative_value(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.opening-cash.store'), [
            'opening_balance' => -50000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['opening_balance']);
    }

    public function test_non_teller_cannot_access_opening_cash_endpoints(): void
    {
        $adminResponse = $this->actingAs($this->admin)->post(route('teller.opening-cash.store'), [
            'opening_balance' => 100000,
        ]);
        $adminResponse->assertForbidden();

        $nasabahResponse = $this->actingAs($this->nasabah)->post(route('teller.opening-cash.store'), [
            'opening_balance' => 100000,
        ]);
        $nasabahResponse->assertForbidden();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    public function test_teller_can_view_opening_cash_status(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')->getJson(route('teller.opening-cash.api.show'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('has_session_opened', false)
            ->assertJsonPath('is_within_operating_hours', true)
            ->assertJsonPath('operating_hours.label', '07:00 - 15:30 WIB');
    }

    public function test_teller_cannot_input_opening_cash_before_7_am_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-03 06:45:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.opening-cash.store'), [
            'opening_balance' => 500000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['opening_balance'])
            ->assertJsonPath('errors.opening_balance.0', 'Sesi loket belum dibuka (Waktu saat ini: 06:45 WIB). Jam operasional sesi loket dimulai pukul 07:00 WIB hingga 15:30 WIB.');
    }

    public function test_teller_cannot_input_opening_cash_after_15_30_pm_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-03 15:31:00', 'Asia/Jakarta'));

        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.opening-cash.store'), [
            'opening_balance' => 500000,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['opening_balance'])
            ->assertJsonPath('errors.opening_balance.0', 'Sesi loket telah berakhir (Waktu saat ini: 15:31 WIB). Jam operasional sesi loket adalah pukul 07:00 hingga 15:30 WIB. Silakan lakukan proses penutupan kas harian.');
    }
}
