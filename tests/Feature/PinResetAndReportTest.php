<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Enums\MemberAccountStatus;
use App\Enums\PinResetStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PinResetAndReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

    protected User $nasabah;

    protected Saver $saver;

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

        $member = Member::create([
            'user_id' => $this->nasabah->id,
            'student_name' => $this->nasabah->name,
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $this->nasabah->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260055',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260055-SCAN',
            'balance' => 75000,
            'status' => SaverStatus::Active,
        ]);
    }

    public function test_forgot_pin_request_generates_token_and_resets_pin_successfully(): void
    {
        // 1. Request reset
        $reqResponse = $this->postJson(route('pin-reset.request'), [
            'account_number' => $this->saver->account_number,
        ]);

        $reqResponse->assertCreated()
            ->assertJsonStructure(['reset_token', 'request']);

        $token = $reqResponse->json('reset_token');
        $this->assertDatabaseHas('pin_reset_requests', [
            'saver_id' => $this->saver->id,
            'reset_token' => $token,
            'status' => PinResetStatus::Pending->value,
        ]);

        // 2. Confirm reset with new PIN
        $confirmResponse = $this->postJson(route('pin-reset.confirm'), [
            'reset_token' => $token,
            'new_pin' => '987654',
            'new_pin_confirmation' => '987654',
        ]);

        $confirmResponse->assertOk();

        // 3. Verify saver PIN updated
        $this->saver->refresh();
        $this->assertTrue(Hash::check('987654', $this->saver->pin));
        $this->assertFalse(Hash::check('123456', $this->saver->pin));
    }

    public function test_monthly_report_and_receipt_endpoints(): void
    {
        $transaction = app(DepositAction::class)->handle(
            teller: $this->teller,
            amount: 50000,
            saver: $this->saver
        );

        // 1. Receipt endpoint
        $receiptResponse = $this->actingAs($this->teller, 'sanctum')->getJson(
            route('reports.receipt', $transaction)
        );

        $receiptResponse->assertOk()
            ->assertJsonPath('receipt.transaction_number', $transaction->transaction_number)
            ->assertJsonPath('receipt.account_number', $this->saver->account_number)
            ->assertJsonPath('receipt.amount', 50000);

        // 2. Monthly report endpoint
        $monthlyResponse = $this->actingAs($this->admin, 'sanctum')->getJson(
            route('reports.monthly', ['year' => date('Y'), 'month' => date('m')])
        );

        $monthlyResponse->assertOk()
            ->assertJsonPath('summary.active_savers_count', 1)
            ->assertJsonPath('summary.total_deposits', 50000);
    }

    public function test_teller_can_lookup_saver_by_qr_code_or_account_number(): void
    {
        $lookupByQr = $this->actingAs($this->teller, 'sanctum')->getJson(
            route('teller.savers.lookup', ['code' => 'QR-BM20260055-SCAN'])
        );

        $lookupByQr->assertOk()
            ->assertJsonPath('saver.account_number', 'BM20260055')
            ->assertJsonPath('saver.qr_code_token', 'QR-BM20260055-SCAN');

        $lookupByAcc = $this->actingAs($this->teller, 'sanctum')->getJson(
            route('teller.savers.lookup', ['code' => 'BM20260055'])
        );

        $lookupByAcc->assertOk()
            ->assertJsonPath('saver.account_number', 'BM20260055');
    }
}
