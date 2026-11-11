<?php

namespace Tests\Feature;

use App\Actions\DepositAction;
use App\Actions\WithdrawAction;
use App\Enums\CashClosureStatus;
use App\Enums\JournalEntryType;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\CashClosure;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CashClosureAndVaultTransferTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teller;

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

        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Siswa Test',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260020',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260020',
            'balance' => 100000,
            'status' => SaverStatus::Active,
        ]);
    }

    public function test_teller_can_calculate_and_submit_daily_cash_closure(): void
    {
        // 1. Process 1 deposit (Rp 50.000) and 1 withdrawal (Rp 20.000)
        app(DepositAction::class)->handle(
            teller: $this->teller,
            amount: 50000,
            saver: $this->saver
        );

        app(WithdrawAction::class)->handle(
            teller: $this->teller,
            saver: $this->saver,
            pin: '123456',
            amount: 20000
        );

        // Net loket cash today: +50.000 - 20.000 = +30.000
        $calcResponse = $this->actingAs($this->teller, 'sanctum')->getJson(route('teller.cash-closure.calculate'));
        $calcResponse->assertOk()
            ->assertJsonPath('preview.total_deposit', 50000)
            ->assertJsonPath('preview.total_withdrawal', 20000)
            ->assertJsonPath('preview.system_ending_balance', 30000);

        // 2. Submit physical count: Rp 30.000 (Exact balance)
        $submitResponse = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.cash-closure.submit'), [
            'physical_ending_balance' => 30000,
            'notes' => 'Kas seimbang di akhir hari kerja.',
        ]);

        $submitResponse->assertCreated()
            ->assertJsonPath('closure.system_ending_balance', 30000)
            ->assertJsonPath('closure.physical_ending_balance', 30000)
            ->assertJsonPath('closure.difference', 0)
            ->assertJsonPath('closure.is_balanced', true)
            ->assertJsonPath('closure.status', CashClosureStatus::Submitted->value);
    }

    public function test_admin_can_approve_cash_closure_which_triggers_vault_transfer(): void
    {
        // Create a submitted closure for the teller with 40.000 physical balance
        $closure = CashClosure::create([
            'teller_id' => $this->teller->id,
            'closure_date' => today(),
            'opening_balance' => 0,
            'total_deposit' => 40000,
            'total_withdrawal' => 0,
            'system_ending_balance' => 40000,
            'physical_ending_balance' => 40000,
            'difference' => 0,
            'status' => CashClosureStatus::Submitted,
        ]);

        $approveResponse = $this->actingAs($this->admin, 'sanctum')->postJson(
            route('admin.api.cash-closures.process', $closure),
            [
                'approve' => true,
            ]
        );

        $approveResponse->assertOk()
            ->assertJsonPath('closure.status', CashClosureStatus::Approved->value);

        // Verify vault transfer journal entries:
        // Debit: Kas Brankas Utama (102) 40.000
        // Credit: Kas Teller (101) 40.000
        $this->assertDatabaseHas('journal_entries', [
            'cash_closure_id' => $closure->id,
            'account_code' => '102',
            'type' => JournalEntryType::Debit->value,
            'amount' => 40000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'cash_closure_id' => $closure->id,
            'account_code' => '101',
            'type' => JournalEntryType::Credit->value,
            'amount' => 40000,
        ]);
    }
}
