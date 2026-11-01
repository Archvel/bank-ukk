<?php

namespace Tests\Feature;

use App\Enums\JournalEntryType;
use App\Enums\MemberAccountStatus;
use App\Enums\Role;
use App\Enums\SaverStatus;
use App\Models\JournalEntry;
use App\Models\Member;
use App\Models\Saver;
use App\Models\User;
use Database\Seeders\AccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WithdrawalAndPinVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $teller;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccountSeeder::class);

        $this->teller = User::factory()->create([
            'role' => Role::Teller,
            'is_active' => true,
        ]);

        $user = User::factory()->create(['role' => Role::Nasabah]);
        $member = Member::create([
            'user_id' => $user->id,
            'student_name' => 'Nasabah Uji',
            'graduation_year' => '2026',
            'account_status' => MemberAccountStatus::Active,
        ]);

        $this->saver = Saver::create([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'account_number' => 'BM20260010',
            'pin' => Hash::make('123456'),
            'qr_code_token' => 'QR-BM20260010',
            'balance' => 50000,
            'status' => SaverStatus::Active,
        ]);
    }

    public function test_withdrawal_fails_with_incorrect_pin(): void
    {
        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.withdraw'), [
            'account_number' => $this->saver->account_number,
            'amount' => 10000,
            'pin' => '999999', // wrong PIN
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['pin']);

        $this->saver->refresh();
        $this->assertSame(50000, $this->saver->balance);
    }

    public function test_withdrawal_fails_when_violating_minimum_maintaining_balance_of_10000(): void
    {
        // Balance is 50.000. Trying to withdraw 45.000 would leave 5.000 (< 10.000)
        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.withdraw'), [
            'account_number' => $this->saver->account_number,
            'amount' => 45000,
            'pin' => '123456',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['amount']);

        $this->saver->refresh();
        $this->assertSame(50000, $this->saver->balance);
    }

    public function test_valid_withdrawal_deducts_balance_and_records_balanced_journals(): void
    {
        // Balance is 50.000. Withdrawing 20.000 leaves 30.000 (>= 10.000)
        $response = $this->actingAs($this->teller, 'sanctum')->postJson(route('teller.transactions.withdraw'), [
            'account_number' => $this->saver->account_number,
            'amount' => 20000,
            'pin' => '123456',
            'notes' => 'Tarik tunai uang saku',
        ]);

        $response->assertCreated()
            ->assertJsonPath('transaction.opening_balance', 50000)
            ->assertJsonPath('transaction.closing_balance', 30000);

        $this->saver->refresh();
        $this->assertSame(30000, $this->saver->balance);

        // Assert double-entry journals:
        // Debit: Simpanan Tabungan (201) 20.000
        // Credit: Kas Teller (101) 20.000
        $this->assertDatabaseHas('journal_entries', [
            'account_code' => '201',
            'type' => JournalEntryType::Debit->value,
            'amount' => 20000,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'account_code' => '101',
            'type' => JournalEntryType::Credit->value,
            'amount' => 20000,
        ]);

        $totalDebit = JournalEntry::where('type', JournalEntryType::Debit)->sum('amount');
        $totalCredit = JournalEntry::where('type', JournalEntryType::Credit)->sum('amount');
        $this->assertSame($totalDebit, $totalCredit);
    }
}
